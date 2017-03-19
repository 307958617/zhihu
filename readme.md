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
### 1、执行composer update命令
    这个命令的作用是将.gitignore忽略的文件如vendor目录重新更新下载到本地
### 2、添加并配置.vue文件，设置如下：
    DB_DATABASE=zhihu;DB_USERNAME=root;DB_PASSWORD=
### 3、生成一个自己的APP_KEY，命令如下：
    php artisan key:generate
### 4、将app目录设置成为Sources Root
    点击右键-选择Mark Directory as-选择Sources Root
### 5、到mysql创建一个名为：zhihu 的数据库
### 6、设计Users表：
    进入项目，修改database->migrations->2014_10_12_create_users_table.php内容如下：
```javascript
   public function up()
       {
           Schema::create('users', function (Blueprint $table) {
               $table->increments('id');
               $table->string('name')->unique();//用户名唯一
               $table->string('email')->unique();//email地址唯一
               $table->string('password');
               $table->string('avatar');//用户头像路径
               $table->string('confirmation_token');//验证邮箱的token
               $table->smallInteger('is_active')->default(0);//用户是否激活了邮箱验证，默认是没激活
               $table->integer('questions_count')->default(0);//用户提问的次数记录
               $table->integer('answers_count')->default(0);//用户回答提问的次数记录
               $table->integer('comments_count')->default(0);//用户留下了多少评论的次数记录
               $table->integer('favorites_count')->default(0);//用户收藏了的次数记录
               $table->integer('likes_count')->default(0);//用户获得点赞的次数记录
               $table->integer('flowers_count')->default(0);//用户关注的次数记录
               $table->integer('flowering_count')->default(0);//用户被关注的次数记录
               $table->json('settings')->nullable();//用户的基本信息，如地址，可以为空
               $table->rememberToken();
               $table->timestamps();
           });
       }
```
### 7、生成Users表：
    php artisan migrate
    如果执行上面命令时候报错： Syntax error or access violation: 1071 Specified key was too long; max key length is 1000 bytes，解决方法如下：
    将config->database.php 设置mysql为'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',之后在数据库中删除表，重新执行上面的migrate命令即可。