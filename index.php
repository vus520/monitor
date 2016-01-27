<?php

//加载配置文件
include(dirname(__FILE__).'/config.php');

//加载代理列表
if (!is_file($proxy))
{
    loger("Loading proxy list");
    include DIR.'proxy.php';
}

$proxy = file($proxy);
$proxy = array_map("trim", $proxy);
$proxy = array_unique($proxy);
if(empty($proxy))
{
    loger("No proxy found");
    exit(1);
}else{
    loger("Load proxy success, total ".count($proxy));
}

//加载任务列表
include DIR.'task.php';
$task = getTask();
if(empty($task))
{
    loger("No task found");
    exit(1);
}else{
    loger("Load task success, total ".count($task));

    //清空进程锁目录
    $dir = DIR."cache/thread/*";
    `rm -f {$dir}`;

    //如何实现异步多线程
    foreach ($task as $job => $tasks)
    {
        foreach ($tasks as $url)
        {
            //进程上限锁
            while (thread_count() > 10)
            {
                sleep(1);
            }

            $pid = pcntl_fork();
            //父进程和子进程都会执行下面代码
            if ($pid == -1) {
                //错误处理：创建子进程失败时返回-1.
                loger("Could not fork new thread !");
                exit(1);
            } else if ($pid) {
                //父进程会得到子进程号，所以这里是父进程执行的逻辑
                pcntl_wait($status, WNOHANG); //等待子进程中断，防止子进程成为僵尸进程。
            } else {
                //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
                $return = runtask($url, $proxy);

                loger($url . "->" . substr($return, 0, strpos($return, "\n")));
                print_r($return);


                //子进程完成一定要结束掉进程，否则进程会越来越多
                exit;
            }

        }
    }
}
exit;