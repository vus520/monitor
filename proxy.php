<?php

if(!defined("DIR"))
    include(dirname(__FILE__).'/config.php');

getJuheapiProxy($proxy);

function getJuheapiProxy($proxy)
{
    $url = "http://japi.juheapi.com/japi/fatch?key=68d6e9f24d53a22e0344b70d72c75462&v=1.0&pkg";

    if(!is_file($proxy))
    {
        $curl = `curl -s "{$url}"`;
        $curl = json_decode($curl, true);

        if(!isset($curl["error_code"]) || $curl["error_code"] > 0)
        {
            loger("Loading proxy fail");
            return false;
        }else{
            file_put_contents($proxy, join("\n", $curl["result"]));
        }
    }
}