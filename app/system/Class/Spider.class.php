<?php

/***
* php spider , jquery ,curl
* 多线程
* in development not stable
*

//取得所有img标签,参数一取属性,参数二格式默认返回DOMElement,参数true返回Array,整形参数返回数组对应下标元素
Spider::query($url)->find('img')->result('src');

*/
class Spider
{

	private static $headers=['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36','Accept: */*'];

	private static $jqueryInstance=[];

	public static function setHeader($key,$value=null)
	{
		if($value)
		{
			self::$headers[]="{$key}: {$value}";
		}
		else if(is_array($key))
		{
			self::$headers=$key;
		}
	}

	public static function query($urls,$timeout=20,$data=null,$encoding=null)
	{
		$html=self::http($urls,$timeout,$data);
		$html=is_array($html)?$html:array($html);
		return self::html($html,$encoding);
	}

	public static function html($html=null,$encoding=null)
	{
		self::resetJqueryInstance();
		$html=is_array($html)?$html:[$html];
		foreach ($html as $item)
		{
			$instance=self::getJqueryInstance($item,$encoding);
			self::$jqueryInstance[]=$instance;
		}
		return $instance;
	}

	public static function jqueryInstanceList()
	{
		return self::$jqueryInstance;
	}

	private static function resetJqueryInstance()
	{
		self::$jqueryInstance=[];
	}

	private static function getJqueryInstance($html=null,$encoding=null)
	{
		return Jquery::ready($html?$html:'<html></html>',$encoding);
	}

	public static function get($urls,$timeout=20)
	{
		return self::http($urls,$timeout);
	}

	public static function post($urls,$timeout=40,$data=[])
	{
		return self::http($urls,$timeout,$data);
	}

	public static function http($urls,$timeout=30,$data=null)
	{
		if(!is_array($urls))
        {
            $ch=curl_init($urls);
            curl_setopt_array($ch,array(CURLOPT_HTTPHEADER=>self::$headers,CURLOPT_FOLLOWLOCATION=>1,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>$timeout,CURLOPT_CONNECTTIMEOUT=>$timeout));
            $data&&curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$data));
            $content=curl_exec($ch);
            curl_close($ch);
            return $content;
        }
        else
        {
            $mh=curl_multi_init();
            foreach ($urls as &$url)
            {
                $ch=curl_init($url);
                curl_setopt_array($ch,array(CURLOPT_HTTPHEADER=>self::$headers,CURLOPT_FOLLOWLOCATION=>1,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>$timeout,CURLOPT_CONNECTTIMEOUT=>$timeout));
                $data&&curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$data));
                curl_multi_add_handle($mh,$ch);
                $url=$ch;
            }
            $runing=null;
            do
            {
                curl_multi_exec($mh,$runing);
                curl_multi_select($mh);
            }
            while($runing>0);
            foreach($urls as &$ch)
            {
                $content=curl_multi_getcontent($ch);
                curl_multi_remove_handle($mh,$ch);
                curl_close($ch);
                $ch=$content;
            }
            curl_multi_close($mh);
            $content=count($urls)>1?$urls:reset($urls);
            return $content;
        }
	}


}


/**
* require >= php5.4
* Jquery::ready($html)->find()->result();
*/
class Jquery
{
	private $xpath;

	private $resultList=[];

	function __construct(DOMXpath $xpath)
	{
		$this->xpath=$xpath;
	}

	public static function ready($html=null,$encoding=null)
	{
		if($html)
		{
			libxml_use_internal_errors(true);
			if($encoding)
			{
				$html="<meta charset='{$encoding}'>".$html;
			}
			if($html instanceof DOMDocument)
			{
				$xpath=new DOMXpath($html);
			}
			else
			{
				$dom=new DOMDocument();
				$dom->preserveWhiteSpace=false;
				$dom->strictErrorChecking=false;
				$ret=$dom->loadHTML($html);
				if($ret)
				{
					$xpath=new DOMXpath($dom);
				}
				else
				{
					$errors=libxml_get_errors();
					libxml_clear_errors();
					return $errors;
				}
			}
			return new self($xpath);
		}
		else
		{
			throw new Exception("Empty html data",1);
		}
	}

	function find($selector='body',$context=null)
	{

		$resultList=&$this->resultList;
		if($resultList)
		{
			//子查询
			if(is_array($resultList))
			{
				$result=[];
				foreach ($resultList as $NodeList)
				{
					//批量中的子查询
					$document=new DOMDocument();
					$document->preserveWhiteSpace=false;
					$document->strictErrorChecking=false;
					for ($i = 0, $length = $NodeList->length; $i < $length; ++$i)
					{
						if ($NodeList->item($i)->nodeType == XML_ELEMENT_NODE)
						{
							$DOMElement=$NodeList->item($i);
							$doc = new DOMDocument();
							$doc->preserveWhiteSpace=false;
							$doc->strictErrorChecking=false;
							$doc->appendChild($doc->importNode($DOMElement,true));
							$xpath=new DOMXpath($doc);
							$ret=$xpath->evaluate(self::selectorToXpath($selector));
							for ($j = 0, $len = $ret->length; $j < $len; ++$j)
							{
								$elem=$ret->item($j);
								$document->appendChild($document->importNode($elem,true));
							}
						}
					}
					$result[]=$document->childNodes;
				}
				$resultList=$result;
				return $this;
			}
			else
			{
				$document=new DOMDocument();
				$document->preserveWhiteSpace=false;
				$document->strictErrorChecking=false;
				for ($i = 0, $length = $resultList->length; $i < $length; ++$i)
				{
					if ($resultList->item($i)->nodeType == XML_ELEMENT_NODE)
					{
						$DOMElement=$resultList->item($i);
						$doc = new DOMDocument();
						$doc->preserveWhiteSpace=false;
						$doc->strictErrorChecking=false;
						$doc->appendChild($doc->importNode($DOMElement,true));
						$xpath=new DOMXpath($doc);
						$ret=$xpath->evaluate(self::selectorToXpath($selector));
						for ($j = 0, $len = $ret->length; $j < $len; ++$j)
						{
							$elem=$ret->item($j);
							$document->appendChild($document->importNode($elem,true));
						}
					}
				}
				$resultList=$document->childNodes;
				return $this;
			}
		}
		else
		{
			if($context)
			{
				$resultList=$this->query($selector);
				return $this;
			}
			else
			{
				$jqueryInstance=Spider::jqueryInstanceList();
				if(count($jqueryInstance)>1)
				{
					foreach ($jqueryInstance as $instance)
					{
						$result=$instance->query($selector);
						$resultList[]=$result;
					}
					return $instance;
				}
				else
				{
					$instance=reset($jqueryInstance);
					$resultList=$instance->query($selector);
					return $instance;
				}
			}
		}

	}


	function result($attr=null,$findstr=null,$callback=null)
	{
		$resultList=&$this->resultList;
		$results=[];
		if(is_array($resultList))
		{
			foreach ($resultList as $item)
			{
				$results[]=self::resultOne($item,$attr,$findstr,$callback);
			}
		}
		else
		{
			$results=self::resultOne($resultList,$attr,$findstr,$callback);
		}
		$this->reset();
		return $results;
	}

	private static function resultOne($NodeList,$attr=null,$findstr=null,$callback=null)
	{
		if(!$NodeList)
		{
			return $NodeList;
		}
		if($attr)
		{
			if(is_int($findstr))
			{
				$item=$NodeList->item($findstr);
				if($item)
				{
					return self::getNodeAttr($item,$attr,$callback);
				}
				return $item;
			}
			else
			{
				$array=[];
				for ($i = 0, $length = $NodeList->length; $i < $length; ++$i)
				{
					if ($NodeList->item($i)->nodeType == XML_ELEMENT_NODE)
					{
						$array[]=self::getNodeAttr($NodeList->item($i),$attr,$callback);
					}
				}
				return $array;
			}
		}
		else
		{
			if($findstr===null)
			{
				return $NodeList;
			}
			else if($findstr===true)
			{
				return self::elementsToArray($NodeList);
			}
			else if(is_int($findstr))
			{
				return self::elementsToArray($NodeList,$findstr);
			}
			return $NodeList;
		}
	}

	private static function getNodeAttr($node,$attr=null,$callback=null)
	{
		$oAttr=$node->attributes->getNamedItem($attr);
		if($oAttr)
		{
			return is_callable($callback)?($callback($oAttr->nodeValue)):$oAttr->nodeValue;
		}
		else if(isset($node->$attr))
		{
			return is_callable($callback)?($callback($node->$attr)):$node->$attr;
		}
		else
		{
			switch (trim($attr))
			{
				case 'html':
				case 'innerhtml':
					$innerHTML='';
					foreach ($node->childNodes as $child)
					{
						$innerHTML .= $node->ownerDocument->saveHTML($child) ?: '';
					}
					return is_callable($callback)?($callback($innerHTML)):$innerHTML;
				case 'outerhtml':
					$doc = new DOMDocument();
					$doc->appendChild($doc->importNode($node,true));
					$outerhtml=$doc->saveHTML($doc);
					return is_callable($callback)?($callback($outerhtml)):$outerhtml;
				case 'text':
				case 'textContent':
					return is_callable($callback)?($callback($node->textContent)):$node->textContent;
				case 'tag':
				case 'tagName':
					return is_callable($callback)?($callback($node->tagName)):$node->tagName;
				case 'attrs':
					$attributes=[];
					if($node->attributes->length)
					{
						foreach($node->attributes as $key => $attr)
						{
							$attributes[$key]=is_callable($callback)?($callback($attr->value)):$attr->value;
						}
					}
					return $attributes;
				default:
					return null;
			}
		}
	}


	function query($selector='body')
	{
		$result=$this->xpath->evaluate(self::selectorToXpath($selector));
		return $result;
	}

	private function reset()
	{
		return $this->resultList=[];
	}

	private static function elementsToArray($result,$idx=null)
	{
		if($idx)
		{
			return self::elementToArray($result->item($idx));
		}
		else
		{
			$array = [];
			for ($i = 0, $length = $result->length; $i < $length; ++$i)
			{
				if ($result->item($i)->nodeType == XML_ELEMENT_NODE)
				{
					$array[]=self::elementToArray($result->item($i));
				}
			}
			return $array;
		}
	}

	private static function elementToArray($element)
	{
		if($element)
		{
			$attributes=[];
			$arr=array('name'=>$element->nodeName,'text'=>$element->textContent,'dom'=>&$element,'attributes'=>&$attributes,'children'=>self::elementsToArray($element->childNodes));
			if($element->attributes->length)
			{
				foreach($element->attributes as $key => $attr)
				{
					$attributes[$key]=$attr->value;
				}
			}
			return $arr;
		}
		return $element;
	}


	private static function selectorToXpath($selector)
	{
		$selector=preg_replace(array('/\s*>\s*/','/\s*~\s*/','/\s*\+\s*/','/\s*,\s*/'),array('>','~','+',','),$selector);
		$selectors=preg_split('/\s+(?![^\[]+\])/',$selector);
		foreach ($selectors as &$selector)
		{
			$selector = preg_replace('/,/','|descendant-or-self::', $selector);
			$selector = preg_replace('/(.+)?:(checked|disabled|required|autofocus)/', '\1[@\2="\2"]', $selector);
			$selector = preg_replace('/(.+)?:(autocomplete)/', '\1[@\2="on"]', $selector);
			$selector = preg_replace('/:(text|password|checkbox|radio|button|submit|reset|file|hidden|image|datetime|datetime-local|date|month|time|week|number|range|email|url|search|tel|color)/', 'input[@type="\1"]', $selector);
			$selector = preg_replace('/(\w+)\[([_\w-]+[_\w\d-]*)\]/', '\1[@\2]', $selector);
			$selector = preg_replace('/\[([_\w-]+[_\w\d-]*)\]/', '*[@\1]', $selector);
			$selector = preg_replace('/\[([_\w-]+[_\w\d-]*)=[\'"]?(.*?)[\'"]?\]/', '[@\1="\2"]', $selector);
			$selector = preg_replace('/^\[/', '*[', $selector);
			$selector = preg_replace('/([_\w-]+[_\w\d-]*)\#([_\w-]+[_\w\d-]*)/', '\1[@id="\2"]', $selector);
			$selector = preg_replace('/\#([_\w-]+[_\w\d-]*)/', '*[@id="\1"]', $selector);
			$selector = preg_replace('/([_\w-]+[_\w\d-]*)\.([_\w-]+[_\w\d-]*)/', '\1[contains(concat(" ",@class," ")," \2 ")]', $selector);
			$selector = preg_replace('/\.([_\w-]+[_\w\d-]*)/', '*[contains(concat(" ",@class," ")," \1 ")]', $selector);
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):first-child/', '*/\1[position()=1]', $selector);
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):last-child/', '*/\1[position()=last()]', $selector);
			$selector = str_replace(':first-child', '*/*[position()=1]', $selector);
			$selector = str_replace(':last-child', '*/*[position()=last()]', $selector);
			$selector = preg_replace('/:nth-last-child\((\d+)\)/', '[position()=(last() - (\1 - 1))]', $selector);
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):nth-child\((\d+)\)/', '*/*[position()=\2 and self::\1]', $selector);
			$selector = preg_replace('/:nth-child\((\d+)\)/', '*/*[position()=\1]', $selector);
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):contains\((.*?)\)/', '\1[contains(string(.),"\2")]', $selector);
			$selector = preg_replace('/>/', '/', $selector);
			$selector = preg_replace('/~/', '/following-sibling::', $selector);
			$selector = preg_replace('/\+([_\w-]+[_\w\d-]*)/', '/following-sibling::\1[position()=1]', $selector);
			$selector = str_replace(']*', ']', $selector);
			$selector = str_replace(']/*', ']', $selector);
		}
		$selector = implode('/descendant::', $selectors);
		$selector = 'descendant-or-self::' . $selector;
		$selector = preg_replace('/(((\|)?descendant-or-self::):scope)/', '.\3', $selector);
		$sub_selectors = explode(',', $selector);
		foreach ($sub_selectors as $key => $sub_selector)
		{
			$parts = explode('$', $sub_selector);
			$sub_selector = array_shift($parts);
			if (count($parts) && preg_match_all('/((?:[^\/]*\/?\/?)|$)/', $parts[0], $matches))
			{
				$results = $matches[0];
				$results[] = str_repeat('/..', count($results) - 2);
				$sub_selector .= implode('', $results);
			}
			$sub_selectors[$key] = $sub_selector;
		}
		$selector = implode(',', $sub_selectors);
		return $selector;
	}

	function __destruct()
	{

	}



}
