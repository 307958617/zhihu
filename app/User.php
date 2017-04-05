<?php
namespace App;

use App\Mailer\UserMailer;
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
        'name', 'email', 'password','avatar','confirmation_token','api_token'
    ];

    public function owns(Model $model)
    {
        return $this->id == $model->user_id;
    }

    public function follows()  //定义users与questions表的多对多关系,表示这个用户关注了多少个问题。
    {
        return $this->belongsToMany(Question::class)->withTimestamps();
    }

    public function followThis($question)
    {
        return $this->follows()->toggle($question);//这就是文档讲的切换关联，即关注与取消关注。
    }

    public function followed($question) //表示关注了这个问题
    {
        return !! $this->follows()->where('question_id',$question)->count(); //注意，这里的两个！表示强制取反，返回是bull值
    }

    public function followers()
    {
        return $this->belongsToMany(self::class,'followers','follower_id','followed_id')->withTimestamps();
    }

    public function followersUser()
    {
        return $this->belongsToMany(self::class,'followers','followed_id','follower_id')->withTimestamps();
    }

    public function followThisUser($user)
    {
        return $this->followers()->toggle($user);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function votes()
    {
        return $this->belongsToMany(Answer::class)->withTimestamps();
    }

    public function vote($answer)//用户对一个问题进行点赞与取消点赞
    {
        return $this->votes()->toggle($answer);
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
        (new UserMailer())->passwordReset($this->email,$token);
    }


}