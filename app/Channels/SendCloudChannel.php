<?php

namespace App\Channels;


use Illuminate\Notifications\Notification;

class SendCloudChannel
{
    public function send($notifiable,Notification $notification)
    {
        $message = $notification->toSendCloud($notifiable);//这里的toSendCloud方法是自己定义的，这要与NewUserFollowNotification里面的方法一致
    }
}