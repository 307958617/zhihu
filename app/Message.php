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

    public function markAsRead()
    {
        if(is_null($this->read_at)){//如果这条私信没有读
            $this->forceFill(['has_read' => 'T','read_at' => $this->freshTimestamp()])->save();//填充字段，标记已读
        }
    }

    public function newCollection(array $models=[])
    {
        return new MessageCollection($models);//这里的这个MessageCollection类还没有写好，需要新建一个类
    }

    public function unRead()//表示如果是未读就返回一个真，
    {
        return $this->has_read === 'F';
    }

    public function shouldAddUnreadClass()//表示应该添加一个未读标志
    {
        if(\Auth::id() === $this->from_user_id){//判断这个用户是私信的发送者
            return false;//表示如果这个用户是私信发送者，就不用标记未读。
        }
        return $this->unRead();
    }
}
