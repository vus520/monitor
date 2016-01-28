<?php

if(!defined("DIR"))
    include(dirname(__FILE__).'/config.php');

function gettask()
{
    $file = glob(DIR."cache/task/*.task");

    $file = array_map("trim", $file);
    $file = array_unique($file);

    $tasks = [];
    foreach ($file as $f) {
        $job = basename($f);
        $task = file($f);
        $task = array_map("trim", $task);
        $task = array_unique($task);

        $tasks[$job] = $task;
    }

    return $tasks;
}

function runtask($url, $proxys)
{
    $md5 = md5($url);

    $result = false;
    $retry  = 1;

    //进程开始，添加进程符号
    thread_count($md5);
    while ($result === false && $retry <= 10)
    {
        $proxy = $proxys[array_rand($proxys)];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,     $url);
        curl_setopt($ch, CURLOPT_PROXY,   $proxy);
        curl_setopt($ch, CURLOPT_HEADER,  1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY,  30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        $code = 0;
        if($result != false)
            $code = getCode($result);

        if($result != false && $code >= 200 && $code < 503)
        {
            //获取响应的头信息，统计业务已经足够了
            $header = format_header($result);
            $header["url"] = $url;
            $header["time"] = time();
            $header["date"] = date("Y-m-d\TH:i:s\Z", $header["time"]);
            list($header["ip"]) = explode(":", $proxy);

            //写入结果
            $dir = DIR."cache/result/".date("Ymd")."/".substr($md5, 0, 2);
            if(!is_dir($dir))
                mkdir($dir, 0777, true);
            $file = sprintf("%s/%s.json", $dir, $md5);
            file_put_contents($file, json_encode($header)."\n", FILE_APPEND | LOCK_EX);
        }else{
            $retry ++;
            $result = false;
        }
    }

    //进程结束，删除进程符号
    thread_count($md5, true);
    return $result;
}

function getCode($str)
{
    $code = substr($str, 9, 3);
    return intval($code);
}

function thread_count($set=null, $delete=null)
{
    $dir  = DIR."cache/thread/";
    $file = glob($dir."*");

    //只获取当前进程数量
    if(!isset($set))
        return count($file);

    //删除符号
    if(isset($delete) && isset($set))
    {
        try {
            @unlink($dir.$set);
        } catch (Exception $e) {

        }
    }

    //新增符号
    if(!isset($delete) && isset($set))
    {
        touch($dir.$set);
    }

    return true;
}

function format_header($string)
{
    $header = ["Content-Type", "Content-Length", "CF-Cache-Status"];

    $match = [];
    $match["code"] = getCode($string);
    list($match["protocol"]) = explode(" {$match['code']} ", $string);

    foreach ($header as $key => $value) {
        preg_match("@{$value}:\s?(.*)@i", $string, $result);
        $match[$value] = trim($result[1]);
    }

    return $match;
}