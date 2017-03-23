<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## 说明：
这是结合laravist网站上关于知乎开发视频的学习记录。

## 步骤一：环境配置和用户表设计
### 1、执行composer update命令
    这个命令的作用是将.gitignore忽略的文件如vendor目录重新更新下载到本地
### 2、添加并配置.vue文件，设置如下：
    DB_DATABASE=zhihu;DB_USERNAME=root;DB_PASSWORD=
### 3、生成一个自己的APP_KEY，命令如下：
    php artisan key:generate
### 4、将app目录设置成为Sources Root
    点击右键-选择Mark Directory as-选择Sources Root
### 5、到mysql创建一个名为：zhihu 的数据库
### 6、设计Users表：
    进入项目，修改database->migrations->2014_10_12_create_users_table.php内容如下：
```javascript
   public function up()
       {
           Schema::create('users', function (Blueprint $table) {
               $table->increments('id');
               $table->string('name')->unique();//用户名唯一
               $table->string('email')->unique();//email地址唯一
               $table->string('password');
               $table->string('avatar');//用户头像路径
               $table->string('confirmation_token');//验证邮箱的token
               $table->smallInteger('is_active')->default(0);//用户是否激活了邮箱验证，默认是没激活
               $table->integer('questions_count')->default(0);//用户提问的次数记录
               $table->integer('answers_count')->default(0);//用户回答提问的次数记录
               $table->integer('comments_count')->default(0);//用户留下了多少评论的次数记录
               $table->integer('favorites_count')->default(0);//用户收藏了的次数记录
               $table->integer('likes_count')->default(0);//用户获得点赞的次数记录
               $table->integer('flowers_count')->default(0);//用户关注的次数记录
               $table->integer('flowering_count')->default(0);//用户被关注的次数记录
               $table->json('settings')->nullable();//用户的基本信息，如地址，可以为空
               $table->rememberToken();
               $table->timestamps();
           });
       }
```
### 7、生成Users表：
    php artisan migrate
    如果执行上面命令时候报错： Syntax error or access violation: 1071 Specified key was too long; max key length is 1000 bytes，解决方法如下：
    将config->database.php 设置mysql为'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',之后在数据库中删除表，重新执行上面的migrate命令即可。
## 步骤二：用户注册
### 1、NauxLiu/Laravel-SendCloud邮件发送驱动安装
    在GitHub搜索NauxLiu/Laravel-SendCloud，用sendcloud邮件服务来发送邮件，里面有具体的安装步骤，按部就班就可以了。不过需要先注册并了解sendcloud。
### 2、命令生成laravel自带的用户注册功能，运行如下命令：
    php artisan make:auth 
### 3、修改通过上面的命令生成的RegisterController注册控制器实现注册时发送邮件：
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
        protected $redirectTo = '/home';
    
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
### 4、实现点击收到的邮件链接，激活邮箱验证功能：
    ① 在web.php路由文件添加一个路由，生产一个邮箱链接地址：
            Route::get('email/verify/{token}',['as' => 'verify.email','uses' => 'EmailController@verify']);
            
    ② 创建一个名为：EmailController 的控制器，实现邮箱的相关验证功能，其内容如下：
        <?php
        
        namespace App\Http\Controllers;
        
        use Illuminate\Support\Facades\Auth;
        use App\User;
        use Illuminate\Http\Request;
        
        class EmailController extends Controller
        {
            public function verify($token)
            {
                $user = User::where('confirmation_token',$token)->first();
                if (is_null($user)){
                    //如果该用户不存在怎么样...
                    return redirect('/');
                }
                $user->is_active = 1;  //激活用户
                $user->confirmation_token = str_random(40); //重置token
                $user->save();
                Auth::login($user);//登录
                return redirect('/home');//跳转
        
            }
        }
        
## 步骤三：用户登录
其实这一步主要是需要将邮箱激活添加到登录验证，即让is_active=1的用户可以登录，让is_active=0的用户不能登录。
### 1、修改LoginController.php里面AuthenticatesUsers.php的attemptLogin方法为：
    protected function attemptLogin(Request $request)
        {
            $credentials = array_merge($this->credentials($request),['is_active' => 1]);//添加is_active字段进去判断是否用邮箱激活
            return $this->guard()->attempt(
                $credentials, $request->has('remember')
            );
        }
**注意：不过最好还是在LoginController里面重写login和attemptLogin方法。** 
### 2、添加一个好用消息提示的功能用于登录或其他方面：
    在GitHub上搜索laracasts/flash，按部就班的安装使用即可。然后想在什么地方用就放到什么地方
    需要注意：需要将下面的代码放到layouts里面的app.blade.php里面以显示
              @if (session()->has('flash_notification.message'))
                   <div class="alert alert-{{ session('flash_notification.level') }}">
                       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
               
                       {!! session('flash_notification.message') !!}
                   </div>
               @endif
    同时在<script src="{{ asset('js/app.js') }}"></script>下面添加如下代码因为是基于jquery的：
        <script>
            $('#flash-overlay-modal').modal();//用于显示模板
            $('div.alert').not('.alert-important').delay(3000).fadeOut(350);//用于控制显示时间
        </script>
## 步骤四、本地化
即将英文显示全部换成中文显示，如注册登录界面改成中文，或验证消息等换成中文。
## 步骤五、修改密码功能（重写）
即在User.php这个model里面重写Illuminate\Auth\Passwords\CanResetPassword.php里面的sendPasswordResetNotification()方法
此方法就用了Illuminate\Auth\Notifications\ResetPassword的notify方法实现发送邮件（因为它继承了Notification），而此方法正好在User.php里面的Notifiable里面。
因此，User.php重写后代码如下：（在这里就直接发送邮件了！）
    
    <?php 
    namespace App;
    
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
## 步骤六、设计问题表
1、创建model和迁移表：

    php artisan make:model Question -m
2、设计表字段：
    
    <?php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class CreateQuestionsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('questions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('body');
                $table->integer('user_id')->unsigned();//关联user表，表示是谁发起的问题
                $table->integer('comments_count')->default(0);//有多少评论
                $table->integer('followers_count')->default(1);//有多少个关注，默认自己发表就关注了所以默认值为1
                $table->integer('answers_count')->default(0);//有多少个回答
                $table->string('close_comment',8)->default('F');//是否关闭评论，默认是没有关闭的
                $table->string('is_hidden',8)->default('F');//是否是隐藏状态，默认不是隐藏（这里的设置有助于管理用户发布的问题）
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('questions');
        }
    }

    