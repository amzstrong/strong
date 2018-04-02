
包含 Http服务 和 定时任务处理


1.分环境处理 [dev,test,staging,pro]

当启动strong_task的时候 需要定义 CRON_ENV  全局变量[dev,test,staging,pro]

http则不需要,http服务根据host带过来的分支和环境自动判断


2.模块化 [例如testa]

新建一个模块就是一个项目,各项目之间相互独立 具体见testa


3.常用函数或者类 common

常用函数或者类 可以写进common,顶层空间名 common

4.一个模块

testa --[
         --config          配置文件
         --controller      控制器
         --crontab         linux定时脚本
         --httpServer      http服务启动脚本
         --taskServer      定时任务处理启动脚本
         --utils           可以写具体业务逻辑
         --index.php       入口文件,加载基础配置
        ]







