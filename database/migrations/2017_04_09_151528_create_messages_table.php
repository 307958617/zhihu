<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_user_id');//发送私信的人的id
            $table->unsignedInteger('to_user_id');//接收私信的人的id
            $table->text('body');//存储私信的具体内容
            $table->string('has_read',8)->default('F');//默认未读
            $table->timestamp('read_at')->nullable();//记录读取这条私信的时间,这是可以为空的
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
        Schema::dropIfExists('messages');
    }
}
