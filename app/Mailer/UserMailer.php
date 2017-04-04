<?php

namespace App\Mailer;


class UserMailer extends Mailer
{
    public function followNotifyEmail($email)  //这个用来重构NewUserFollowNotification里面的发送邮件方法
    {
        $data = [
            'url' => 'http://zhihu',
            'name'=> \Auth::guard('api')->user()->name,
        ];//注意：这里面的变量名与sendcloud里面的变量名必须一致。
        $this->sendTo('new_user_follow',$email,$data);
    }

    public function passwordReset($email,$token) //这将替换User model里面的sendPasswordResetNotification方法发送邮件
    {
        $data = [
            'url' => route('password.reset',$token),
        ];//注意：这里面的变量名与sendcloud里面的变量名必须一致。
        $this->sendTo('ZhiHu_Modify_Password',$email,$data);
    }

    public function welcome(User $user)  //替换Http/Controllers/Auth/RegisterController里面的sendVerifyEmailTo方法
    {
        $data = [
            'url' => route('verify.email',['token' => $user->confirmation_token]),
            'name' => $user->name
        ];
        $this->sendTo('zhihu_dev_register',$user->email,$data);
    }
}