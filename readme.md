<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## 说明：
这是结合laravist网站上关于知乎开发视频的学习记录。

## 步骤一：环境配置和用户表设计
###1、执行composer update命令
    这个命令的作用是将.gitignore忽略的文件如vendor目录重新更新下载到本地
###2、添加并配置.vue文件，设置如下：
    DB_DATABASE=zhihu;DB_USERNAME=root;DB_PASSWORD=
###3、生成一个自己的APP_KEY，命令如下：
    php artisan key:generate
###4、将app目录设置成为Sources Root
    点击右键-选择Mark Directory as-选择Sources Root
###5、到mysql创建一个名为：zhihu 的数据库