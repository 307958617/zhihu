<?php

namespace App\Mailer;

use Mail;
use Naux\Mail\SendCloudTemplate;

class Mailer  //这里主要是作为一个基类存在，实现基本的功能，供其他类调用的
{
    protected function sendTo($template,$email,array $data)// $template：SendCloud的邮件模板名称;$email:email地址;$data：要传的数据
    {
        $content = new SendCloudTemplate($template, $data);//这里需要在SendCloud重新设置一个用户关注的邮件模板

        Mail::raw($content, function ($message) use ($email){
            $message->from('307958617@qq.com', 'Laravel');
            $message->to($email);
        });
    }
}