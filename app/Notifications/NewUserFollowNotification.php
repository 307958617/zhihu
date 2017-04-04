<?php

namespace App\Notifications;

use App\Channels\SendCloudChannel;
use App\Mailer\Mailer;
use App\Mailer\UserMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Naux\Mail\SendCloudTemplate;

class NewUserFollowNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database',SendCloudChannel::class];
    }

    public function toSendCloud($notifiable)
    {
//        $data = [
//            'url' => 'http://zhihu',
//            'name'=> \Auth::guard('api')->user()->name,
//        ];//注意：这里面的变量名与sendcloud里面的变量名必须一致。
//        $template = new SendCloudTemplate('new_user_follow', $data);//这里需要在SendCloud重新设置一个用户关注的邮件模板
//
//        Mail::raw($template, function ($message) use ($notifiable){
//            $message->from('307958617@qq.com', 'Laravel');
//            $message->to($notifiable->email);
//        });
        (new UserMailer())->followNotifyEmail($notifiable->email);
    }

    public function toDatabase($notifiable)
    {
        return [
            'name' => \Auth::guard('api')->user()->name
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
