<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Mail;//不要忘记添加这里的代码
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Naux\Mail\SendCloudTemplate; //不要忘记添加这里的代码

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'avatar' => '/images/avatars/default.png', //此文件是放在public目录下面的
            'confirmation_token' => str_random(40), //生成邮箱验证的随机token字符串
            'password' => bcrypt($data['password']),
            'api_token' => str_random(60) // 生成用于api验证的随机token字符串
        ]);//这里需要注意：将avatar和confirmation_token这两个字段添加到User Model的fillable里面
        $this->sendVerifyEmailTo($user);
        return $user;
    }

    /**
     * 引入SendCloud,通过它来发送邮件
     */

    public function sendVerifyEmailTo($user)
    {
        $data = [
            'url' => route('verify.email',['token' => $user->confirmation_token]),
            //注意：此处的verify.email是下面第四步②里面创建的路由。
            'name' => $user->name
        ];//注意：这里面的变量名与sendcloud里面的变量名必须一致。
        $template = new SendCloudTemplate('zhihu_dev_register', $data);

        Mail::raw($template, function ($message) use($user) {
            $message->from('307958617@qq.com', 'Laravel');

            $message->to($user->email);
        });
    }
}