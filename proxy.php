<?php

if(!defined("DIR"))
    include(dirname(__FILE__).'/config.php');

getFreeProxy($proxy);
//getJuheapiProxy($proxy);

function getFreeProxy($proxy)
{
    $urls = [
        "http://www.kuaidaili.com/free/outha/1/",
        "http://www.kuaidaili.com/free/outha/2/",
        "http://www.kuaidaili.com/free/outtr/1/",
        "http://www.kuaidaili.com/free/outtr/2/",
    ];

    $url = sprintf('"%s"', join('" "', $urls));

    $curl = `curl -s {$url}`;
    preg_match_all("@<td>(?<ip>[\d\.]+)</td>[\s\S]*?<td>(?<port>[\d]+)</td>@is", $curl, $match);

    if(count($match["ip"]) > 0)
    {
        $data = [];
        foreach($match["ip"] as $key => $ip)
        {
            $data[] = $match["ip"][$key] .":". $match["port"][$key];
        }

        file_put_contents($proxy, join("\n", $data));
    }
}

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