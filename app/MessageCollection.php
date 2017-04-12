<?php

namespace App;


use Illuminate\Database\Eloquent\Collection;

class MessageCollection extends Collection
{
    public function markAsRead()//注意，这里的方法必须要与Message model里面的方法名字一样
    {
        $this->each(function ($message){
            if($message->to_user_id === \Auth::id()){//表示只有私信的接收者读了才能标记已读。
                $message->markAsRead();//这个方法就是在Message model里面定义的方法，就可以实现对每一条message执行这个方法了
            }
        });
    }
}