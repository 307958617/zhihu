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
                $table->longText('body'); //不能用string，不然字段长度不够
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
3、迁移表到数据库中：

    php artisan migrate
## 步骤七、实现发布问题功能
### 1、引入Laravel-UEditor这个富文本编辑器，具体步骤在GitHub上搜索overtrue/laravel-ueditor按部就班即可。
注意：在引入之前，需要创建发布问题的视图：
#### ①在views目录下创建一个文件夹：questions
#### ②在questions文件夹下面创建一个名为create.blade.php的视图文件用来发布问题，代码如下： 
    @extends('layouts.app')
    
    @section('content')
        @include('vendor.ueditor.assets') {{--这行的作用是引入编辑器需要的 css,js 等文件，所以你不需要再手动去引入它们--}}
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">发布问题</div>
                        <div class="panel-body">
                            <form action="/questions" method="post" role="form">
                                {{csrf_field()}}
                                <div class="form-group">
                                    <label for="title">问题名称</label>
                                    <input type="text" class="form-control" name="title" placeholder="请输入问题名称">
                                </div>
                                <div class="form-group">
                                    <label for="title">问题内容</label>
                                    <!-- 编辑器容器 -->
                                    <script id="container" name="body" type="text/plain"></script>
                                </div>
                                <button type="submit" class="btn btn-primary pull-right">发布问题</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 实例化编辑器 -->
        <script type="text/javascript">
            var ue = UE.getEditor('container');
            ue.ready(function() {
                ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
            });
        </script>
    @endsection
#### ③创建一个resource类型的控制器名为：QuestionsController。
    php artisan make:controller QuestionsController --resource
#### ④进入QuestionsController控制器文件，修改create方法为：
    public function create()
    {
        return view('questions.create');//返回发布问题的视图
    }
     
#### ⑤进入web.php路由文件添加一个资源路由：
    Route::resource('questions','QuestionsController',['names'=>[  //命名路由
        'create' => 'questions.create',// 用于显示提交问题的表单的页面
        'show' => 'questions.show'  //用于发布问题后显示问题的页面
    ]]);
#### ⑥进入QuestionsController控制器文件，修改store方法为：//用于表单提交并保持到数据库
    public function store(Request $request)
    {
        $date = [
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'user_id' => Auth::id()  //表示是谁发布的问题
        ];
        $question = Question::create($date);//保持到数据库中
        return redirect(route('questions.show',[$question->id]));//跳转到问题显示页面，[]里面的内容是这个文章的ID
    }
注意：不要忘记在Question model里面填写$fillable字段。

    protected $fillable = ['title','body','user_id'];    
#### ⑦进入QuestionsController控制器文件，修改show方法为：
    public function show($id)
    {
        $question = Question::find($id);
        return view('questions.show',compact('question'));//传递到视图
    }
#### ⑧在questions文件夹下面创建一个名为show.blade.php的视图用于显示发布的问题：
    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">{{ $question->title }}</div>
    
                        <div class="panel-body">
                            {!! $question->body !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
### 2、实现发布问题的表单验证：
方法一：直接在控制器：QuestionsController里面的create方法里面的最前面添加如下代码即可
    
    $rulers = [
        'title' =>'required|min:6',
        'body' =>'required|min:16'
    ];//验证的规则
    $messages = [
        'title.required' => '我是自定义的提示，不能为空哦！'
    ];//自定义提示消息
    $this->validate($request,$rulers,$messages);//靠这个方法来实现验证
方法二：创建一个名为：StoreQuestionRequest的request来实现验证：
    
    php artisan make:request StoreQuestionRequest
执行之后就会在app/Http目录下面出现一个Requests的文件夹，它里面就是刚刚创建的这个request，就可以在里面写验证代码了：
    
    <?php
    
    namespace App\Http\Requests;
    
    use Illuminate\Foundation\Http\FormRequest;
    
    class StoreQuestionRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize()
        {
            return true;
        }
    
        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules()
        {
            return [
                'title' =>'required|min:6',
                'body' =>'required|min:16'
            ];
        }
    
        public function messages()
        {
            return [
                'title.required' => '我是自定义的提示，不能为空哦！'
            ];
        }
    }
然后只需要到QuestionsController里面在store方法里面依赖注入StoreQuestionRequest即可：
    
    public function store(StoreQuestionRequest $request)//这里是唯一需要修改的地方
    {
        $date = [
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'user_id' => Auth::id()  //表示是谁发布的问题
        ];
        $question = Question::create($date);//保持到数据库中
        return redirect(route('questions.show',[$question->id]));//跳转到问题显示页面，[]里面的内容是这个文章的ID
    }


最后：如果需要显示错误提示信息，并且填好了的字段内容不消失，就需要在实现了方法1或方法2后修改create.blade.php代码为如下：
    
    @extends('layouts.app')
    
    @section('content')
        @include('vendor.ueditor.assets') {{--这行的作用是引入编辑器需要的 css,js 等文件，所以你不需要再手动去引入它们--}}
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">发布问题</div>
                        <div class="panel-body">
                            <form action="/questions" method="post" role="form">
                                {{csrf_field()}}
                                <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">{{--这是为了显示错误时的样式，显示为红色--}}
                                    <label for="title">问题名称</label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title') }}" placeholder="请输入问题名称">{{--这是为了保留原来提交的数据，避免用户重填--}}
                                    @if ($errors->has('title'))    {{--这段就是为了显示相应的提示信息--}}
                                        <span class="help-block">
                                            <strong>{{ $errors->first('title') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="form-group{{ $errors->has('body') ? ' has-error' : '' }}">
                                    <label for="body">问题内容</label>
                                    <!-- 编辑器容器 -->
                                    <script id="container" name="body" type="text/plain">
                                        {{ old('body') }}  {{--这里要注意，不是放在里面，而是用放到标签之间--}}
                                    </script>
                                    @if ($errors->has('body'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('body') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-primary pull-right">发布问题</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 实例化编辑器 -->
        <script type="text/javascript">
            var ue = UE.getEditor('container');
            ue.ready(function() {
                ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
            });
        </script>
    @endsection
 
### 3、美化、简化编辑器
  1、clone 代码（自己下载下来后放到了网盘里面直接用也行）
  
    git clone https://github.com/JellyBool/simple-ueditor.git
  2.用此项目的 ueditor 目录替换原来的 ueditor 目录。
    
  3.实例化编辑器的时候配置 toolbar ，主要是 toolbar 的配置（具体配置可参考UEditor官方文档）
    
    <script type="text/javascript">
        var ue = UE.getEditor('container',{
            toolbars: [
                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
            ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode:true,
            wordCount:false,
            imagePopup:false,
            autotypeset:{ indent: true,imageBlockLine: 'center' }
        });
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>
### 4、完善发布问题页面，不让未登录的用户发布问题。
只要在QuestionsController里面添加一个构造方法即可，代码如下：
    
    public function __construct()
    {
        $this->middleware('auth')->except('index','show');//表示除了index和show展示页面不需要登录，其他需要登录才行
    }
### 5、定义话题与问题关系（即问题与话题是多对多关系）。
①生成话题model和迁移表：
    
    php artisan make:model Topic -m
②生成话题表即Topic迁移文件内容为：
    
    public function up()
        {
            Schema::create('topics', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');//话题名字
                $table->text('bio')->nullable();//话题简介
                $table->integer('questions_count')->default(0);//这个话题下面有多少个问题的总数
                $table->integer('followers_count')->default(0);//这个话题的关注者数量
                $table->timestamps();
            });
        }
③同时到Topic model里面添加$fillable，同时定义与questions表的多对多关系:
    
    class Topic extends Model
    {
        protected $fillable = ['name','bio','questions_count','follower_count'];
    
        public function questions()//定义与questions表的多对多关系
        {
            return $this->belongsToMany(Question::class,'中间表名，这个参数可以自定义')->withTimestamps();
        }
    }
④同时在Question model 添加与topics表的多对多关系添加如下代码：
    
    public function topics()
    {
        return $this->belongsToMany(Topic::class,'中间表名，这个参数可以自定义')->withTimestamps();
    }
⑤添加questions表与topics表的中间表：
    
    php artisan make:migration create_questions_topics_table --create=question_topic //--create是指定哪两个model对应表的关联表名
    中间表内容为：
    public function up()
    {
        Schema::create('question_topic', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id')->unsigned()->index();
            $table->integer('topic_id')->unsigned()->index();
            $table->timestamps();
        });
    }
⑥执行migrate命令，插入刚刚创建的两张表到数据库：
    
    php artisan migrate
⑦最后在创建问题question的时候(本例是在QuestionsController里面的store方法里面)，指定相关的topic，并写入中间表：
    
    $question->topics()->attach($topics);//attach方法就可以关联了。
### 6、引入select2，优化话题选择：
①下载select2依赖的两个文件select2.min.css，select2.min.js文件到本地resources目录下面的assets目录
②需要先运行npm install 下载各种包以便后面使用npm run dev等命令编译下载的两个文件到app.js和app.css里面
③引入刚刚下载的js和css方法：
1、打开resources/assets/js/bootstrap.js文件，在里面添加如下代码：
    
    require('./select2.min');//这是引入select2.min.js
2、打开resources/assets/sass/app.scss文件，在里面添加如下代码：

    @import "./../css/select2.min.css";//这是引入select2.min.css
④执行编译命令进行编译：
    
    npm run dev
⑤到需要用到的视图表单里面插入如下代码：（本例是在questions/create.blade.php里）
**注意：到这里要修复一个bug，在公共模板layout/app.blade.php最后添加一个@yield('js')
    然后将questions/create.blade.php中@include('vendor.ueditor.assets')代码放到
    @section('js')与@endsection之间**
添加的代码select2代码：

    <div class="div form-group{{ $errors->has('topic') ? ' has-error' : '' }}">
        <label for="topic">选择话题</label>
        <select class="js-example-basic-multiple form-control" multiple="multiple">
            <option value="AL">Alabama</option>
            <option value="WY">Wyoming</option>
        </select>
    </div>
同时在script标签里面添加：

    $(".js-example-basic-multiple").select2();
