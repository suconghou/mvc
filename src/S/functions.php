<?php

//扩展函数库

//php 异步执行
function async($url)
{
$ch = curl_init(); 
$curl_opt = array(CURLOPT_URL=>$url,CURLOPT_TIMEOUT=>1);
curl_setopt_array($ch, $curl_opt);
curl_exec($ch);
curl_close($ch);
}