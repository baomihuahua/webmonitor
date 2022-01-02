## 网站监控 for qqbot

简单的 php curl 目标网站监控状态后返回状态码，如果错误代码大于 400 以上均为异常报错；

### 搭建

1.需要一个支持 PHP 的环境，支持 curl，一个go-cqhttp机器人

2.把文件放入 

3.修改

```
config.json
```

为你要监控的网站，和通知的 QQ 号码

修改

```
qq.php 70行 127.0.0.1:5700 为你的机器人api地址
```
4.把qq.php加入计划任务，至于间隔多少分钟执行一次你自己安排，间隔执行也就是间隔监控时间

![](https://cdn.jsdelivr.net/gh/baomihuahua/boxmoeimg@0a08f6a8a81674aae5420467edb79c09e15a501b/2022/01/02/2d408f647a2f23e4d89b2884d381405b.png)



### 相关搭建资料
- [官方博客](https://www.boxmoe.com/)
- [go-cqhttp机器人搭建](https://www.boxmoe.com/522.html)
