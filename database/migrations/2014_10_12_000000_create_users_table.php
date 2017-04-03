<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
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
            $table->integer('followers_count')->default(0);//用户关注的次数记录
            $table->integer('following_count')->default(0);//用户被关注的次数记录
            $table->json('settings')->nullable();//用户的基本信息，如地址，可以为空
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
