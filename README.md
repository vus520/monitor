一个通过代理检测http[s] url是否正常的简单监控
---------------

一个通过代理检测http[s] url是否正常的简单监控，已实现多代理、多线程、多任务的功能

目前暂无数据可视化的实现

###安装依赖###

1. Linux + PHP
2. PHP需要安装pcntl扩展


###代理池来源###

1. https://www.juhe.cn/docs/api/id/62
2. http://www.kuaidaili.com/pricing/

###执行任务###

1. 添加cache/task/xxx.task，一行一个url
2. php index.php