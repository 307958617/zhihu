<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['from_user_id','to_user_id','body','dialog_id'];

    public function fromUser()//定义私信与发送私信用户的关系
    {
        return $this->belongsTo(User::class,'from_user_id');
    }

    public function toUser()//定义私信与接收私信用户的关系
    {
        return $this->belongsTo(User::class,'to_user_id');
    }
}
