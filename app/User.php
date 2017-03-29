<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;
use Naux\Mail\SendCloudTemplate;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','avatar','confirmation_token'
    ];

    public function owns(Model $model)
    {
        return $this->id == $model->user_id;
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','confirmation_token'
    ];

    public function sendPasswordResetNotification($token)
    {
        $data = [
            'url' => route('password.reset',$token),
        ];//注意：这里面的变量名与sendcloud里面的变量名必须一致。
        $template = new SendCloudTemplate('ZhiHu_Modify_Password', $data);//这里需要在SendCloud重新设置一个修改密码的邮件模板

        Mail::raw($template, function ($message){
            $message->from('307958617@qq.com', 'Laravel');
            $message->to($this->email);
        });
    }


}