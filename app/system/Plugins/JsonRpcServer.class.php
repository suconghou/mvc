<?php

/**
* JsonRpcServer
*/
class JsonRpcServer
{
	private static $payload=array();

	private static $allowIp=array();

	private static $allowUser=array();

	function __construct($request=null)
	{
		if($request)
		{
			self::$payload=json_decode($request,true);
		}
		else
		{
			self::$payload=json_decode(file_get_contents('php://input'),true);
		}
		return $this->init();
	}

	function init()
	{
		if(self::$allowIp and !in_array($_SERVER['REMOTE_ADDR'],self::$allowIp))
		{
			$response=self::forbiddenResponse();
		}
		else if(self::$allowUser and !(isset(self::$allowUser[self::getUsername()]) and self::$allowUser[self::getUsername()]==self::getPassword()))
		{
			$response=self::authenticationFailureResponse();
		}
		else
		{
			$response=self::execute();
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}



	/**
	 * get the response
	 */
	private static function execute()
	{
		if(self::$payload and is_array(self::$payload))
		{
			try
			{
				if(self::isBatchRequest())
				{
					return self::handleBatchRequest();
				}
				list($method,$params)=self::checkRpcFormat();
				return self::response(self::executeProcedure($method,$params),self::$payload);
			}
			catch(InvalidJsonRpcFormat $e)
			{
				return self::response(array('error'=>array('code'=>-32600,'message'=>'Invalid Request')),array('id'=>null));
			}
			catch(BadFunctionCallException $e)
			{
				return self::response(array('error'=>array('code'=>-32601,'message'=>'Method not found')),self::$payload);
			}
			catch(InvalidArgumentException $e)
			{
				return self::response(array('error'=>array('code'=>-32602,'message'=>'Invalid params')),self::$payload);
			}
			catch(Exception $e)
			{
				return self::response(array('error'=>array('code'=>$e->getCode(),'message'=>$e->getMessage())),self::$payload);
			}

		}
		else
		{
			return array('error'=>'Malformed payload');
		}
	}

	private static function handleBatchRequest()
	{
		$payload=self::$payload;
		$responses=array();
		foreach($payload as $load)
		{
			self::$payload=$load;
			$response=self::execute();
			$responses[]=$response;
		}
		return $responses;
	}

	private static function executeProcedure($procedure,array $params=array())
	{
		$procedureInfo=explode('.',$procedure);
		$namespace=count($procedureInfo)>2?array_shift($procedureInfo):null;
		list($class,$method)=$procedureInfo;
		if(class_exists($class))
		{
			$instance=new $class;
			if(method_exists($instance,$method))
			{
				$result=call_user_func_array(array($instance,$method),$params);
				if(is_null($result))
				{
					$result=ob_get_clean();
				}
				return array('result'=>$result);
			}
		}
		throw new BadFunctionCallException('Unable to find the procedure');
	}

	private static function executeMethod($class,$method,$params)
	{

	}

	private static function executeCallback(Closure $callback,$params)
	{

	}

	private static function getUsername()
	{
		return isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:null;
	}
	
	private static function getPassword()
	{
		return isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:null;
	}

	private static function isBatchRequest()
	{
		 return array_keys(self::$payload) === range(0, count(self::$payload) - 1);
	}

	private static function checkRpcFormat()
	{
		if(!isset(self::$payload['method'],self::$payload['params'],self::$payload['jsonrpc']) or !is_array(self::$payload['params']) or count(explode('.',self::$payload['method']))<2)
		{
			throw new InvalidJsonRpcFormat('Invalid JSON RPC payload');
		}
		return array(self::$payload['method'],self::$payload['params']);
	}

	private static function forbiddenResponse()
	{
		header('HTTP/1.0 403 Forbidden');
		return  array('error'=>'Access Forbidden');
	}

	private static function authenticationFailureResponse()
	{
		header('WWW-Authenticate: Basic realm="JsonRPC"');
		header('HTTP/1.0 401 Unauthorized');
		return array('error'=>'Authentication failed');
	}

	private static function response(array $data,array $payload=array())
	{
		$id=isset($payload['id'])?$payload['id']:null;
		$response=array('id'=>$id,'jsonrpc'=>'2.0');
		return array_merge($response,$data);
	}
}



class InvalidJsonRpcFormat extends Exception {};


