<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->longText('body'); //不能用string，不然字段长度不够
            $table->integer('user_id')->unsigned();//关联user表，表示是谁发起的问题
            $table->integer('comments_count')->default(0);//有多少评论
            $table->integer('followers_count')->default(1);//有多少个关注，默认自己发表就关注了所以默认值为1
            $table->integer('answers_count')->default(0);//有多少个回答
            $table->string('close_comment',8)->default('F');//是否关闭评论，默认是没有关闭的
            $table->string('is_hidden',8)->default('F');//是否是隐藏状态，默认不是隐藏（这里的设置有助于管理用户发布的问题）
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
        Schema::dropIfExists('questions');
    }
}
