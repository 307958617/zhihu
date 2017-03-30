<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();//关联到users表，表示是由谁创建的答案
            $table->integer('question_id')->unsigned()->index();//关联到questions表，表示是哪个问题的答案
            $table->longText('body');//表示答案的内容
            $table->integer('votes_count')->default(0);//表示该答案被点赞的总数
            $table->integer('comments_count')->default(0);//表示该答案有多少个评论
            $table->string('is_hidden',8)->default('F');//表示该答案是否隐藏，默认不隐藏
            $table->string('close_comment',8)->default('F');//表示该答案是否关闭了评论，默认不关闭
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
        Schema::dropIfExists('answers');
    }
}
