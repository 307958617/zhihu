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
        <select name="topics[]" id="topic" class="form-control" multiple="multiple">//这里需要注意，name必须使用数组形式，即topics[]
        </select>
        @if ($errors->has('topic'))
            <span class="help-block">
                <strong>{{ $errors->first('topic') }}</strong>
            </span>
        @endif
    </div>
同时在script标签里面添加：

    //下面是select2的引入
            $(document).ready(function () {
                $('#topic').select2({
                    placeholder:'select a topic',
                    tags:true,//表示可以自己添加输入的值
                    minimumInputLength: 1,
                    ajax:{
                        url:'/api/topics',//api的路径
                        dataType:'json',
                        delay:250,
                        data:function (params) {
                            return {
                                q:params.term   //q代表传递到api的参数值，与$request->query('q')中的q对应
                            }
                        },
                        processResults:function (data) {
                            return {
                                results:$.map(data.items,function (id,name) {//data.items就是api查询后传递回来的json数据即response()->json(['items'=>$topics])
                                    return {id:id,text:name};                 //这里的$.map是遍历传过来的数据好显示到select2选择框
                                })
                            };
                        }
                    }
                });
            });
⑥到api.php 路由文件添加如下代码：
    
    Route::middleware('api')->get('/topics', function (Request $request) {
        $topics = \App\Topic::where('name','like','%'.$request->query('q').'%')
                                                   ->pluck('id','name');//特别注意，这里需要用pluck()方法来提取数据
        return response()->json(['items'=>$topics]);//这里的items就是传递到视图的data.items
    });
⑦处理使用select2传递过来的topics中自己定义的话题，即如果传过来的不是数字，就重新创建一个topic并返回改topic的id值即可：
1、先在QuestionsController.php里面新建一个方法：
    
    private function normalizeTopics(array $topics){
        return collect($topics)->map(function ($topic){ //通过collect()和map()方法遍历数组
            if (is_numeric($topic)){   //如果是数字就返回数字
                Topic::find($topic)->increment('questions_count');//将话题关联的问题数量+1
                return (int)$topic;
            }
            $newTopic = Topic::create(['name'=>$topic,'questions_count'=>1]);//如果不是数字就表示没有这个话题因此新建一个topic，将问题数设置为1
            return $newTopic->id;//返回这个topic的id值
        })->toArray();//转换成数组
    }
2、然后在QuestionsController.php里的store方法里面使用normalizeTopics()方法：
    
    $topics = $this->normalizeTopics($request->topics);//这里的topics就是select2传递过来的数组数据
3、最后在QuestionsController.php里的store方法里面将关联关系写入中间表：
    
    $question->topics()->attach($topics);//将关联关系写入中间表.这里的$topics就是话题id组成的数组
### 7、将topics显示到问题页面，只需要改一句代码即可：
    
    public function show($id)
    {
        $question = Question::where('id',$id)->with('topics')->first();//这里with('topics')传递到视图引用
        return view('questions.show',compact('question'));//传递到视图
    }
    
  之后视图引用通过循环输出即可：
    
    @foreach($question->topics as $topic)
        {{ $topic->name }}
    @endforeach
### 8、使用Repository模式重构代码：即可以将model和控制器controller分开并且可以提高代码的可维护和可读性,它主要是操作model的CRUD
①到app目录下面创建名为：Repositories的文件夹，用于放置各种repository

②在Repositories文件夹下创建一个名为：QuestionRepository.php的class，注意，这里的名称最好是model名+Repository

这里修改QuestionsController里面的show方法为例：QuestionRepository.php代码如下：
    
    <?php
    
    namespace App\Repositories;
    
    
    use App\Question;
    
    class QuestionRepository
    {
        public function findQuestionById_withTopics($id)//这个方法是自己定义的，要求就是可读性强
        {
            return Question::where('id',$id)->with('topics')->first();
        }
    }
③将QuestionRepository.php通过依赖注入方式引入QuestionsController里面，代码如下：
    
    protected $questionRepository;
        
    public function __construct(QuestionRepository $questionRepository)
    {
        $this->middleware('auth')->except('index','show');//表示除了index和show展示页面不需要登录，其他需要登录才行（原来已有的代码）
        $this->questionRepository = $questionRepository;//依赖注入QuestionRepository
    }
④重写QuestionsController里面的show方法如下：
    
    public function show($id)
    {
        $question = $this->questionRepository->findQuestionById_withTopics($id);
        return view('questions.show',compact('question'));//传递到视图
    }
同理修改其他也一样，主要是讲model与controller分离。
## 步骤八、实现编辑问题、显示问题列表、删除问题：
### 1、编辑问题：
①创建编辑问题的视图文件：edit.blade.php,代码可以参考create.blade.php进行修改即可：需要修改的地方用《修》标识

    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">发布问题</div>
                        <div class="panel-body">
                            <form action="/questions/{{$question->id}}" method="post" role="form">  {{--《修》修改提交表单的地址--}}
                                {{method_field('PATCH')}}  {{--《修》修改提交表单的方式--}}
                                {{csrf_field()}}
                                <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">{{--这是为了显示错误时的样式，显示为红色--}}
                                    <label for="title">问题名称</label>
                                    <input type="text" class="form-control" name="title" value="{{ $question->title }}" placeholder="请输入问题名称">{{--这是为了保留原来提交的数据，避免用户重填--}}{{--《修》修改显示数据--}}
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
                                        {{ $question->body }}  {{--这里要注意，不是放在里面，而是用放到标签之间--}}{{--《修》修改显示数据--}}
                                    </script>
                                    @if ($errors->has('body'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('body') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="div form-group{{ $errors->has('topic') ? ' has-error' : '' }}">
                                    <label for="topic">选择话题</label>
                                    <select name="topics[]" id="topic" class="form-control" multiple="multiple">
                                        @foreach($question->topics as $topic)     {{--《修》修改显示数据--}}
                                            <option value="{{$topic->id}}" selected="selected">{{$topic->name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('topic'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('topic') }}</strong>
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
    @endsection
    
    @section('js')
        @include('vendor.ueditor.assets')
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
            //下面是select2的引入
            $(document).ready(function () {
                $('#topic').select2({
                    placeholder:'select a topic',
                    tags:true,//表示可以自己添加输入的值
                    minimumInputLength: 1,
                    ajax:{
                        url:'/api/topics',//api的路径
                        dataType:'json',
                        delay:250,
                        data:function (params) {
                            return {
                                q:params.term   //q代表传递到api的参数值，与$request->query('q')中的q对应
                            }
                        },
                        processResults:function (data) {
                            return {
                                results:$.map(data.items,function (id,name) {//data.items就是api查询后传递回来的json数据即response()->json(['items'=>$topics])
                                    return {id:id,text:name};                 //这里的$.map是遍历传过来的数据好显示到select2选择框
                                })
                            };
                        }
                    }
                });
            });
        </script>
    @endsection
②添加edit方法代码：
    
    public function edit($id)
    {
        $question = $this->questionRepository->findQuestionById_withTopics($id);
        if(Auth::user()->owns($question)){         //判断用户是否是文章的发布者，如果是才能看到编辑界面，否则就跳转回去。这里的owns()方法需要到User model里面添加
            return view('questions.edit',compact('question'));
        }
        flash('对不起，你不是作者不能编辑该文章！');
        return back();
    }
    
  User.php model里添加的owns()方法代码如下：这里很关键要注意
    
    public function owns(Model $model)
    {
        return $this->id == $model->user_id ;
    }
③添加update方法代码如下：

    public function update(StoreQuestionRequest $request, $id)//和store方法的验证规则一样
    {
        $question = $this->questionRepository->findQuestionById_withTopics($id);
        $question->update([
            'title'=>$request->get('title'),
            'body' =>$request->get('body')
        ]);
        $topics = $this->questionRepository->normalizeTopics($request->topics);
        $question->topics()->sync($topics);//将关联关系写入中间表，注意：这里需要将attach方法改为sync方法，同步修改。
        return redirect(route('questions.show',[$question->id]));//跳转到问题显示页面，[]里面的内容是这个文章的ID
    }
④有一个问题就是关于话题表里面的问题数量，只会增加不会减少的问题怎么解决？？？
可以不用在topics表里面的questions_count字段，自己直接在QuestionRepository里面添加一个方法：
    
    public function getNumbOfQuestions_byTopicId($id)
    {
        $topic = Topic::withCount('questions')->find($id);
        return $topic->questions_count;
    }   
### 2、显示问题列表：
①创建显示问题列表的视图questions/index.blade.php,代码内容：
    
    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    @foreach($questions as $question)
                        <div class="media">//这是媒体对象的样式
                            <div class="media-left">
                                <a href="">
                                    <img style="border-radius:50%" src="{{$question->user->avatar}}" alt="{{$question->user->name}}">
                                </a>
                            </div>
                            <div class="media-body">
                                <h4>
                                    <a href="/questions/{{$question->id}}">{{ $question->title }} </a>//显示具体文章的页面
                                </h4>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endsection
②QuestionsController里面的index()方法代码如下：

    public function index()
    {
        $questions = $this->questionRepository->getAllQuestions();//这里是得到所有问题
        return view('questions.index',compact('questions'));
    }
    
  getAllQuestions()方法在QuestionRepository里面代码如下：
    
    public function getAllQuestions()
    {
        return Question::latest('updated_at')->get();
    }
  
③引入query-Scope方法，实现自定义条件查询。虽然第②步没问题，但是这里需要引入一个限制，即如果问题表questions的is_hidden字段为T，那么就不能显示这条问题。
###### 第一步：在Question model里面添加：
    
    public function scopePublished($query)//定义发布限制条件，注意名称的写法 小写的scope+第一个字母大写的Published，采用驼峰法。
    {
        return $query->where('is_hidden','F')->get();
    }
###### 第二步：在QuestionRepository里面新写一个方法获取所有的问题：

    public function getAllQuestions_published()
    {
        return Question::published()->latest('update_at')->get();//这里的published()方法就是Question model的scopePublished()方法变化而来，其实就是一个东西。
    }
###### 第三步：修改②QuestionsController里面的index()方法，代码如下：

    public function index()
    {
        $questions = $this->questionRepository->getAllQuestions_published();
        return view('questions.index',compact('questions'));
    }
### 3、删除问题：
①在QuestionRepository里面新写一个方法：
    
    public function delQuestionById($id)
    {
        Question::destroy($id);
    }
②在QuestionsController里面的destroy()方法使用delQuestionById，代码如下：
  
    public function destroy($id)
    {
        $this->questionRepository->delQuestionById($id);
        return redirect('/questions');
    }
③在show.blade.php这个视图里面添加入一个删除按钮：
    
    <div class="action">
        @if(Auth::check() && Auth::user()->owns($question)) {{--这是判断权限的如只有登录并且是这个问题的发起者成能删除它，这里的owns()方法在上面也提到过，写在User model里面的--}}
            <form action="/questions/{{$question->id}}" method="post">
                {{method_field('DELETE')}}//这里需要注意，使用的是DELETE方法才能触发destory()方法
                {{csrf_field()}}
                <button class="btn" style="background:transparent;color: red">删除</button> {{--transparent这是一个css样式,背景透明--}}
            </form>
        @endif
    </div>
## 步骤九：创建问题答案、提交问题答案、显示问题答案：
### 1、创建问题答案：
①新建名为：Answer 的model：

    php artisan make:model Answer -m
②设计答案表Answers的字段：
    
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();//关联到users表，表示是由谁创建的答案
            $table->integer('question_id')->unsigned()->index();//关联到questions表，表示是哪个问题的答案
            $table->longText('body');//表示答案的内容
            $table->integer('votes_count')->default(0);//表示该答案被点赞的总数
            $table->integer('comments_count')->default(0);//表示该答案有多少个评论
            $table->string('is_hidden',8)->default('F');//表示该答案是否隐藏，默认不隐藏
            $table->string('close_comment',8)->default('F');//表示该答案是否关闭了评论，默认不关闭
            $table->timestamps();
        });
    }
③写入answer表到数据库：
       
    php artisan migrate
④定义answers表与users表和questions表的相互关系：
   1、Answer model里面定义与问题表questions，用户表users的关联关系：
        
        protected $fillable = ['user_id','question_id','body'];
        
        class Answer extends Model
        {
            public function question()
            {
                return $this->belongsTo(Question::class);
            }
        
            public function user()
            {
                return $this->belongsTo(User::class);
            }
        }
   2、在Question与User model里面定义与answer的关联关系：
        
        public function answers()
        {
            return $this->hasMany(Answer::class);
        }
### 2、提交问题答案：
①需要创建一个答案的controller名为：AnswersController：
    
    php artisan make:controller AnswersController --resource//虽然是resource但是这里只需要用到store方法。
②需要在web.blade.php路由文件添加一条到AnswersController，store()方法的路由。
    
    Route::post('questions/{question}/answers/store',['as'=>'answers.store','uses'=>'AnswersController@store']);
③在questions/show.blade.php 视图下面添加一个提交问题答案的表单，添加后代码如下：
    
    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ $question->title }}
                            @foreach($question->topics as $topic)
                                <div class="badge">{{ $topic->name }}</div>
                            @endforeach
                        </div>
    
                        <div class="panel-body">
                            {!! $question->body !!}
                        </div>
                        <div class="action">
                            @if(Auth::check() && Auth::user()->owns($question)) {{--这是判断权限的如只有登录并且是这个问题的发起者成能删除它--}}
                                <form action="/questions/{{$question->id}}" method="post">
                                    {{method_field('DELETE')}}
                                    {{csrf_field()}}
                                    <button class="btn" style="background:transparent;color: red">删除</button> {{--transparent这是一个css样式,背景透明--}}
                                </form>
                            @endif
                        </div>
                    </div>
    {{--下面是实现问题答案提交功能代码--}}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            共有 {{ $question->answers_count }} 个答案
                        </div>
    
                        <div class="panel-body">
                            显示答案部分待完成...
                            <div class="action">
                                @if(Auth::check()) {{--这是判断权限的如只有登录的用户才能回答问题添加答案--}}
                                <form action="/questions/{{$question->id}}/answers/store" method="post"> {{--注意提交的地址--}}
                                    {{csrf_field()}}
                                    <div class="form-group{{ $errors->has('answer') ? ' has-error' : '' }}">
                                        <!-- 编辑器容器 -->
                                        <script id="container" name="answer" type="text/plain">
                                            {{ old('answer') }}  {{--这里要注意，不是放在里面，而是用放到标签之间--}}
                                        </script>
                                        @if ($errors->has('answer'))
                                            <span class="help-block">
                                            <strong>{{ $errors->first('answer') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <button class="btn btn-primary pull-right">发布答案</button> {{--transparent这是一个css样式,背景透明--}}
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
    {{--上面是实现问题答案提交功能代码--}}
                </div>
            </div>
        </div>
    @endsection
    {{--下面是实现问题答案提交功能的富文本框依赖程序--}}
    @section('js')
        @include('vendor.ueditor.assets')
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
    @endsection
    {{--上面是实现问题答案提交功能的富文本框依赖程序--}}
④新建一个AnswerRepository.php,里面处理与Answer model的交互，代码如下：
    
    <?php
    
    namespace App\Repositories;
        
    use App\Answer;  
    
    class AnswerRepository
    {
        public function createAnswer(array $answer)
        {
            return Answer::create($answer);
        }
    }
⑤在AnswersController里面引用repository，代码如下：
    
    <?php
    
    namespace App\Http\Controllers;
    
    use App\Repositories\AnswerRepository;
    use Illuminate\Http\Request;
    
    class AnswersController extends Controller
    {
        protected $answerRepository;
    
        public function __construct(AnswerRepository $answerRepository)
        {
            $this->answerRepository = $answerRepository;
        }
    
        public function store(Request $request,$question)
        {
            $answer = $this->answerRepository->createAnswer([  //保存答案到数据库
                'user_id' => \Auth::id(),
                'question_id' => $question,
                'body' => $request->body
            ]);
            $answer->question()->increment('answers_count');//同时将问题表里面的答案字段answers_count +1
            return back();
        }
    }
### 3、显示问题答案：
在questions/show.blade.php 视图下面的 “显示答案部分待完成...”部分添加如下代码：

    {{--下面是显示问题答案的代码--}}
    @foreach($question->answers as $answer)
        <div class="media">
            <div class="media-left">
                <a href="">
                    <img style="border-radius:50%" src="{{$answer->user->avatar}}" alt="{{$answer->user->name}}">
                </a>
            </div>
            <div class="media-body">
                <h4 class="media-heading">
                    <a href="">{{$answer->user->name}}</a>
                </h4>
                {!!  $answer->body  !!}
            </div>
        </div>
    @endforeach
    {{--上面是显示问题答案的代码--}}
## 步骤十、实现用户关注问题：
①在show.blade.php视图添加一个用户关注的模块：show.blade.php修改后代码如下：

    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-1">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ $question->title }}
                            @foreach($question->topics as $topic)
                                <div class="badge">{{ $topic->name }}</div>
                            @endforeach
                        </div>
    
                        <div class="panel-body">
                            {!! $question->body !!}
                        </div>
                        <div class="action">
                            @if(Auth::check() && Auth::user()->owns($question)) {{--这是判断权限的如只有登录并且是这个问题的发起者成能删除它--}}
                                <form action="/questions/{{$question->id}}" method="post">
                                    {{method_field('DELETE')}}
                                    {{csrf_field()}}
                                    <button class="btn" style="background:transparent;color: red">删除</button> {{--transparent这是一个css样式,背景透明--}}
                                </form>
                            @endif
                        </div>
                    </div>
    {{--下面是实现问题答案提交功能代码--}}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            共有 {{ $question->answers_count }} 个答案
                        </div>
    
                        <div class="panel-body">
    {{--下面是显示问题答案的代码--}}
                            @foreach($question->answers as $answer)
                                <div class="media">
                                    <div class="media-left">
                                        <a href="">
                                            <img style="border-radius:50%" src="{{$answer->user->avatar}}" alt="{{$answer->user->name}}">
                                        </a>
                                    </div>
                                    <div class="media-body">
                                        <span class="media-heading">
                                            <a href="">{{$answer->user->name}}</a>
                                        </span>
                                        {!!  $answer->body  !!}
                                    </div>
                                </div>
                            @endforeach
    {{--上面是显示问题答案的代码--}}
                            <div class="action">
                                @if(Auth::check()) {{--这是判断权限的如只有登录的用户才能回答问题添加答案--}}
                                <form action="/questions/{{$question->id}}/answers/store" method="post"> {{--注意提交的地址--}}
                                    {{csrf_field()}}
                                    <div class="form-group{{ $errors->has('body') ? ' has-error' : '' }}">
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
                                    <button class="btn btn-primary pull-right">发布答案</button> {{--transparent这是一个css样式,背景透明--}}
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
    {{--上面是实现问题答案提交功能代码--}}
                </div>
    {{--下面是关注问题模块--}}
                <div class="col-md-3">
                    <div class="panel panel-default" style="text-align: center">
                        <div class="panel-heading">
                            <h1>{{$question->followers_count}}</h1>
                            <span>关注者</span>
                        </div>
                        <div class="panel-body">
                            <a href="/questions/{{ $question->id }}/follow" class="btn btn-default">关注该问题</a>
                            <a href="#editor" class="btn btn-primary">撰写答案</a>
                        </div>
                    </div>
                </div>
    {{--上面是关注问题模块--}}
            </div>
        </div>
    @endsection
    {{--下面是实现问题答案提交功能的富文本框依赖程序--}}
    @section('js')
        @include('vendor.ueditor.assets')
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
    @endsection
    {{--上面是实现问题答案提交功能的富文本框依赖程序--}}
②创建用户与问题的之间的多对多关系中间表（也就是关注表）：question_user 表：
  
  1、创建迁移文件：
  
    php artisan make:migration crate_question_user_table --create=question_user //创建迁移文件
  2、修改迁移文件，内容如下：
    
    public function up()
    {
        Schema::create('question_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->timestamps();
        });
    }
  3、在数据库添加该表：
      
     php artisan migrate
③为上面的中间表question_user表创建一个model来操作这个表：
    
    php artisan make:model Follow
    内容如下：
    class Follow extends Model
    {
        protected $table = 'question_user';
        
        protected $fillable = ['question_id','user_id'];
    }
    注意：本来到这里之后，应该到users表和questions表定义关联关系，但是这里我们有更好的办法，直接在User model添加一个follows()方法更加简单方便
④在User model 添加follows()方法：
    
    public function follows($question)  //这里直接创建一个关注的数据即可
    {
        return Follow::create([
            'question_id'=>$question,
            'user_id' => $this->id
        ]);
    }
⑤在web.blade.php路由文件创建一个新的路由：
    
    Route::get('questions/{question}/follow','QuestionFollowController@follow');
⑥创建一个控制器：QuestionFollowController：
    
    php artisan make:controller QuestionFollowController
    内容如下：
    <?php
    
    namespace App\Http\Controllers;
    
    use Auth;
    use Illuminate\Http\Request;
    
    class QuestionFollowController extends Controller
    {
        public function follow($question)
        {
            Auth::user()->follows($question); //这里的follows方法就是在User model里面创建的方法。
            return back();
        }
    }
⑦注意，上面第④步有一个问题，就是点了关注就会+1，而不能取消关注，这里需要修改上面方法，还是需要定义两个表之间的关系：
    
    在User model定义与questions表的多对多关系，修改follows()方法为：
    public function follows()  //定义users与questions表的多对多关系
    {
        return $this->belongsToMany(Question::class)->withTimestamps();
    }
    同时添加如下方法：
    public function followThis($question)
    {
        return $this->follows()->toggle($question);//这就是文档讲的切换关联
    }
    最后在QuestionFollowController控制器里将follow()方法改为：followThis()方法即可；（其实这里可以看出，上面创建的Follow model就没有上面用了，可以忽略了）
⑧注意，这里还有个问题呢，就是关注按钮的样式不会自动切换，这里就需要判断，根据question_user表是否有数据来切换样式：
在User model里面再添加一个方法：followed()方法：
    
    public function followed($question) //表示关注了这个问题
    {
        return !! $this->follows()->where('question_id',$question)->count(); //注意，这里的两个！表示强制取反，返回是bull值
    }
    然后修改show.blade.php中的关注按钮的样式：
    <a href="/questions/{{ $question->id }}/follow" class="btn btn-default {{Auth::user()->followed($question->id)?'btn-success':''}}">
        {{Auth::user()->followed($question->id)?'取消关注':'关注该问题'}}
    </a>
⑨还有一个地方需要注意，只有登录的用户才能关注问题，那么就需要在QuestionFollowController控制器添加一个构造方法，添加Auth这个middleware：
    
    public function __construct()
    {
        $this->middleware('auth');
    }
⑩还有一个就是某个问题关注者的人数，是在问题表里面的followers_count字段提出来的，但是这个不是很好的方法。用withCount方法才比较好：
    
    1、创建一个FollowRepository.php的class：
        <?php
        
        namespace App\Repositories;
        
        use App\Question;
        
        class FollowRepository
        {
            public function getNumbOfFollowers_byQuestionId($question)
            {
                $q = Question::withCount('followers')->find($question);
                return $q->followers_count;
            }
        }
        
    2、在QuestionsController里面，依赖注入上面的FollowRepository,代码如下：
        protected $questionRepository;
        protected $followRepository;
    
        public function __construct(QuestionRepository $questionRepository,FollowRepository $followRepository)
        {
            $this->middleware('auth')->except('index','show');//表示除了index和show展示页面不需要登录，其他需要登录才行
            $this->questionRepository = $questionRepository;//依赖注入QuestionRepository
            $this->followRepository = $followRepository; //依赖注入FollowRepository
        }
    3、再修改在QuestionsController里面的show方法，代码如下：
        
        public function show($id)
        {
            $question = $this->questionRepository->findQuestionById_withTopics($id);
            $followersCount = $this->followRepository->getNumbOfFollowers_byQuestionId($question->id);//传递问题的关注者数量到视图
            return view('questions.show',compact('question','followersCount'));//传递到视图
        }
## 步骤十一、在laravel中使用Vue.js实现组件化：
### 1、首先需要让phpstorm支持Vue.js：
    在设置->plugins->输入vue.js然后点击查询，然后安装之后->重新启动phpstorm即可。
### 2、laravel项目里面使用vue主要操作的目录位置为：
    resources->assets->js里面的东西
### 3、将show.blade.php中的关注按钮做成一个组件，步骤如下：
①在resources/assets/js/components目录下面创建一个名为：QuestionFollowButton.vue的文件，这即为关注按钮组件，内容如下：
    
    <template>
        <button class="btn btn-default"
                :class="{'btn-success':followed}"
                v-text="text"
                @click="follow"
        >
        </button>
    </template>
    
    <script>
        export default {
            props:['question','user'],//这里的数据就是从show.blade.php视图里面传递进来的两个值
            data() {
                return {
                    followed:false
                }
            },
            mounted() {
                axios.post('http://zhihu/api/question/follower',{'q':this.question,'u':this.user}).then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                    this.followed = response.data.followed;
                    console.log(response.data.followed)
                }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
            },
            computed: { //计算属性
                text() {
                    return this.followed ? '取消关注':'关注该问题'
                }
            },
            methods:{
                follow() {
                    axios.post('http://zhihu/api/question/follow',{'q':this.question,'u':this.user}).then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                        this.followed = response.data.followed;
                        console.log(response.data.followed)
                    }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
                }
            }
        }
    </script>
②在resources/assets/js下的app.js文件里面注册上面创建的组件：即添加下面一行代码到里面即可
    
    Vue.component('question_follow_button', require('./components/QuestionFollowButton.vue'));
③到show.blade.php文件关注按钮的位置，引入注册的这个组件，即：

    <div class="panel-body">
        {{--<a href="/questions/{{ $question->id }}/follow" class="btn btn-default {{Auth::user()->followed($question->id)?'btn-success':''}}">--}}
            {{--{{Auth::user()->followed($question->id)?'取消关注':'关注该问题'}}--}}
        {{--</a>--}}
        <question_follow_button question="{{ $question->id }}" user="{{ Auth::id() }}"></question_follow_button> //用这个组件替换上面的<a>标签的按钮，这里的两个参数是要传到QuestionFollowButton.vue里面的props里面的。
        <a href="#editor" class="btn btn-primary">撰写答案</a>
    </div>
④需要执行npm run dev进行编译之后才能正常显示，不然要报错。 
⑤不要忘记在api路由文件里面添加一个路由，给①来调用：
    
    Route::middleware('api')->post('question/follower', function (Request $request) {
        $followed = \App\Follow::where('question_id',$request->get('q'))->where('user_id',$request->get('u'))->count();
        if($followed > 0)
            return response()->json(['followed'=> true]);
        else
            return response()->json(['followed'=> false]);
    });
    
    Route::middleware('api')->post('question/follow', function (Request $request) {
        $followed = \App\Follow::where('question_id',$request->get('q'))->where('user_id',$request->get('u'))->first();
        if($followed !== null){//如果这条数据不为空，则说明存在关注，那么点击就会将这条记录删除然后返回一个false
            $followed->delete();
            return response()->json(['followed'=> false]);
        }
        //如果为空，则说明没有关注，我们就需要添加一条记录然后返回true
        \App\Follow::create([
            'question_id' => $request->get('q'),
            'user_id' => $request->get('u')
        ]);
        return response()->json(['followed'=> true]);
    });
### 4、实现登录验证：如果第⑤步中的api路由添加一个middleware('auth'),那么就会报错401 (Unauthorized)，解决方法如下：
①为users表添加一个api_token字段：
    
    php artisan make:migration add_api_token_to_users_table --table=users 
    该迁移文件其内容如下：
    public function up()
    {//添加一个字段api_token
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_token',64)->unique();
        });
    }

    public function down()
    {//删除一个字段api_token
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['api_token']);
        });
    }
②执行php artisan migrate，在数据库的users表插入一个api_token字段
**这里需要注意：在注册用户的时候就需要为api_token字段自动生成一个随机字符串了，使用'api_token' => str_random(60)**

③如何使用api_token?
    
    1、在resources/assets/js/bootstrap.js文件里面的window.axios.defaults.headers.common = {}里面添加一行：'Authorization': window.Laravel.apiToken,添加后代码如下：
    window.axios.defaults.headers.common = {
        'X-CSRF-TOKEN': window.Laravel.csrfToken,
        'Authorization': window.Laravel.apiToken,//这就是新添加的一行
        'X-Requested-With': 'XMLHttpRequest'
    };
    2、在views/layouts/app.blade.php文件里面添加一行代码：Laravel.apiToken = "{{Auth::check() ? 'Bearer '.Auth::user()->api_token :'Bearer '}}"位置是在：
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
        Laravel.apiToken = "{{Auth::check() ? 'Bearer '.Auth::user()->api_token :'Bearer '}}" //这就是添加的一行
    </script>
④最后执行：npm run dev进行编译一遍即可解决上面报错的问题了。
⑤最后的最后，有了上面的步骤，就可以在api中通过下面的方法得到请求api的用户数据：
    
    return Auth::guard('api')->user();
## 步骤十二、实现用户之间的关注，即用户关注用户：
### 1、创建一个用户之间关注信息的表：
    php artisan make:migration create_followers_table --create=followers
    内容如下：
    public function up()
    {
        Schema::create('followers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('follower_id')->unsigned()->index();//表示关注者，即发起关注的人的id
            $table->integer('followed_id')->unsigned()->index();//表示被关注者的id
            $table->timestamps();
        });
    }
    然后执行：
    php artisan migrate //将表写入数据库中
### 2、到User model里面声明一个多对多关系，即用户表与用户表自己的关系：
    注意：这里需要定义两个相反的
    public function followers()
    {
        return $this->belongsToMany(self::class,'followers','follower_id','followed_id')->withTimestamps();
    }
    public function followersUser()
    {
        return $this->belongsToMany(self::class,'followers','followed_id','follower_id')->withTimestamps();
    }
### 3、在show.blade.php里面添加显示关注用户的视图代码：
    
    {{--下面是关注用户模块--}}
    <div class="col-md-3">
        <div class="panel panel-default" style="text-align: center">
            <div class="panel-heading">
                <h5>关于作者</h5>
            </div>
            <div class="panel-body">
                <div class="media">
                    <div class="media-left">
                        <a href="">
                            <img style="border-radius:50%" src="{{$question->user->avatar}}" alt="{{$question->user->name}}">
                        </a>
                    </div>
                    <div class="media-body">
                        <h5 class="media-heading">
                            <a href="">{{$question->user->name}}</a>
                        </h5>
                    </div>
                </div>
                <div class="user-status" style="display: flex;margin-top: 20px">
                    <div class="status-item" style="padding: 2px 20px;">
                        <div class="status-text">问题</div>
                        <div class="status-count">{{$question->user->questions_count}}</div>
                    </div>
                    <div class="status-item" style="padding: 2px 20px;">
                        <div class="status-text">回答</div>
                        <div class="status-count">{{$question->user->answers_count}}</div>
                    </div>
                    <div class="status-item" style="padding: 2px 20px;">
                        <div class="status-text">关注者</div>
                        <div class="status-count">{{$question->user->followers_count}}</div>
                    </div>
                </div>
                <question_follow_button question="{{ $question->id }}" user="{{ Auth::id() }}"></question_follow_button>
                <a href="#editor" class="btn btn-default">发送私信</a>
            </div>
        </div>
    </div>
    {{--上面是关注用户模块--}}
### 4、实现关注用户按钮的组件化
①在resources/assets/js/components目录下创建一个名为：UserFollowButton.vue的文件，内容如下：
    
    <template>
        <button class="btn btn-default"
                :class="{'btn-success':followed}"
                v-text="text"
                @click="follow"
        >
        </button>
    </template>
    
    <script>
        export default {
            props:['user'],//这里的数据就是从show.blade.php视图里面传递进来的两个值
            data() {
                return {
                    followed:false
                }
            },
            mounted() {
                axios.get('http://zhihu/api/user/followers/' + this.user).then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                    this.followed = response.data.followed;
                    console.log(response.data.followed)
                }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
            },
            computed: { //计算属性
                text() {
                    return this.followed ? '取消关注':'关注他(她)'
                }
            },
            methods:{
                follow() {
                    axios.post('http://zhihu/api/user/follow',{'u':this.user}).then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                        this.followed = response.data.followed;
                        console.log(response.data.followed)
                    }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
                }
            }
        }
    </script>

②在api路由文件注册两条路由给UserFollowButton.vue的axios请求用内容分别为：
    
    Route::middleware('auth:api')->get('/user/followers/{id}','FollowersController@index');
    Route::middleware('auth:api')->post('/user/follow','FollowersController@follow');
③创建一个FollowersController，并添加index与follow方法，内容如下：
    
    先执行：php artisan make:controller FollowersController
    内容为：
    <?php
    
    namespace App\Http\Controllers;
    
    use App\Follower;
    use App\User;
    use Illuminate\Http\Request;
    
    class FollowersController extends Controller
    {
        public function index($id)
        {
            $user = User::find($id);//这表示这个问题的作者
            $followers = $user->followersUser()->pluck('follower_id')->toArray();//表示这个问题作者的关注者的id有哪些
            if(in_array(\Auth::guard('api')->user()->id,$followers)){  //判断登录的用户的id是否在关注者id列表里面
                return response()->json(['followed'=>true]);
            }
            return response()->json(['followed'=>false]);
        }
    
        public function follow(Request $request)
        {   //注意：这里需要创建一个名为Follower的model才行
            $userToFollow = User::find($request->get('u'));//这表示被关注的用户，即该问题的作者
            $authUser = \Auth::guard('api')->user();
            $followed = Follower::where('follower_id',$authUser->id)->where('followed_id',$userToFollow->id)->first();
            if($followed !== null){
                $followed->delete();
                $userToFollow->decrement('followers_count');
                return response()->json(['followed'=> false]);
            }
            Follower::create([
                'follower_id' => $authUser->id,
                'followed_id' => $userToFollow->id
            ]);
            $userToFollow->increment('followers_count');
            return response()->json(['followed'=> true]);
        }
    }
④到User model 里面添加一个方法：
    
    public function followThisUser($user)
    {
        return $this->followers()->toggle($user);
    }
⑤到resources/assets/js/app.js里面注册UserFollowButton组件：
    
    Vue.component('user_follow_button', require('./components/UserFollowButton.vue'));
⑥执行：npm run dev
## 步骤十二、实现站内信通知和给被关注用户发送邮件用notification的方法：
### 1、实现站内信通知：
①创建一个名为：NewUserFollowNotification的notification文件：
    
    执行：php artisan make:notification NewUserFollowNotification
    内容为：
    <?php
    
    namespace App\Notifications;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Notifications\Notification;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Messages\MailMessage;
    
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
            return ['database'];
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

②为站内信的实现提供一个table：
    
    执行：php artisan notifications:table
③执行php artisan migrate 将上面创建的表写入数据库
④在FollowersController里面的follow()方法下面添加一行代码用于发送站内信通知：
    
    $userToFollow->notify(new NewUserFollowNotification());//发送一个站内信通知告诉被关注的人有人关注你了。
⑤创建一个视图文件index.blade.php用于显示站内信通知：
    
    在resources/views/下面创建一个名为notifications的文件夹，里面放置所有的notification
    这里创建一个名为：index.blade.php,内容为：
    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">消息通知</div>
    
                        <div class="panel-body">
                            @foreach($user->notifications as $notification)
                                {{ $notification->data['name'] }}
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

⑥为上面的视图文件创建一个路由：
    
    Route::get('/notifications', 'NotificationsController@index');
⑦创建名为NotificationsController的控制器：
    
    php artisan make:controller NotificationsController
    内容为：
    public function index()
    {
        $user = \Auth::user();
        return view('notifications.index',compact('user'));
    }
到此为止，就可以登录显示通知了。

⑧实现根据不同的notification的type值，显示不同的视图文件的方法：
    
    将index.blade.php的内容改为：
    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">消息通知</div>
    
                        <div class="panel-body">
                            @foreach($user->notifications as $notification)
                                @include('notifications.'.snake_case(class_basename($notification->type)))
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    然后在notifications下创建一个名为：new_user_follow_notification.blade.php，这个名字就是根据type提出来的，其内容为：
    <li class="notifications">
        {{ $notification->data['name'] }} 关注了你！
    </li>
### 2、实现发送邮件功能：
注意：如果想使用sendCloud发送邮件，就不能用laravel自带的mail来发送了，这里就需要自定义channel来实现用sendCloud发送邮件：
①在app/目录下创建一个Channels文件夹，然后在里面创建一个名为：SendCloudChannel的class,内容为：
    
    <?php
    
    namespace App\Channels;
    
    
    use Illuminate\Notifications\Notification;
    
    class SendCloudChannel
    {
        public function send($notifiable,Notification $notification)//这里的send方法很重要
        {
            $message = $notification->toSendCloud($notifiable);//这里的toSendCloud方法是自己定义的，这要与NewUserFollowNotification里面的方法一致
        }
    }
②在NewUserFollowNotification里面新建一个方法toSendCloud来实现发送邮件：
    
    public function via($notifiable)
    {
        return ['database',SendCloudChannel::class];
    }

    public function toSendCloud($notifiable)
    {
        $data = [
            'url' => 'http://zhihu',
            'name'=> \Auth::guard('api')->user()->name,
        ];//注意：这里面的变量名与sendcloud里面的变量名必须一致。
        $template = new SendCloudTemplate('new_user_follow', $data);//这里需要在SendCloud重新设置一个用户关注的邮件模板

        Mail::raw($template, function ($message) use ($notifiable){
            $message->from('307958617@qq.com', 'Laravel');
            $message->to($notifiable->email);
        });
    }
    
## 步骤十三、重构邮件发送代码：
### 1、在app目录下面创建一个Mailer文件夹,然后在里面创建一个Mailer的基础class，内容如下：
    
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
### 2、在app目录下面Mailer文件夹下创建名为：UserMailer.class的类，它继承Mailer，
    
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
### 3、分别将其他地方发送邮件的代码替换如下：
①将NewUserFollowNotification里的toSendCloud()方法改成如下：
    
    public function toSendCloud($notifiable)
    {
        (new UserMailer())->followNotifyEmail($notifiable->email);
    }
②将User model里面的sendPasswordResetNotification方法改写如下：
    
    public function sendPasswordResetNotification($token)
    {
        (new UserMailer())->passwordReset($this->email,$token);
    }
③将Http/Controllers/Auth/RegisterController里面的sendVerifyEmailTo方法替换如下：

    public function sendVerifyEmailTo($user)
    {
        (new UserMailer())->welcome($user);
    }
## 步骤十四、实现问题答案的点赞功能：
### 分析：点赞功能其实就是登录用户与答案的关系因此：
### 1、创建一个migration文件，表名为：answer_user
    
    php artisan make:migration crate_answer_user_table --create=answer_user
    其内容为：
    public function up()
    {
        Schema::create('answer_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('answer_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->timestamps();
        });
    }
### 2、分别到User Model和Answer Model里面定义两个表之间的多对多关联关系：
    
    User Model里面的：
    public function votes()
    {
        return $this->belongsToMany(Answer::class)->withTimestamps();
    }

    public function vote($answer)//用户对一个问题进行点赞与取消点赞
    {
        return $this->votes()->toggle($answer);
    }
    Answer Model里面的:
    public function users()// 点赞关系
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
### 3、创建一个UserVoteButton.vue的组件：
①到components文件夹里面创建名为UserVoteButton.vue的组件，内容为：
    
    <template>
        <button class="btn btn-default"
                :class="{'btn-success':followed}"
                v-text="text"
                @click="follow"
        >
        </button>
    </template>
    
    <script>
        export default {
            props:['answer'],//这里的数据就是从show.blade.php视图里面传递进来的值
            data() {
                return {
                    followed:false,
                    count:0
                }
            },
            mounted() {
                axios.get('http://zhihu/api/answer/' + this.answer + '/votes/').then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                    this.followed = response.data.followed;
                    this.count = response.data.count;
                    console.log(response.data.count)
                }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
            },
            computed: { //计算属性
                text() {
                    return this.count;
                }
            },
            methods:{
                follow() {
                    axios.post('http://zhihu/api/answer/vote',{'answer':this.answer}).then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                        this.followed = response.data.followed;
                        this.followed ? this.count ++ : this.count --;
                        console.log(response.data)
                    }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
                }
            }
        }
    </script>

②在resources/assets/js/app.js文件里注册上面新建的UserVoteButton.vue组件：
    
    Vue.component('user_vote_button', require('./components/UserVoteButton.vue'));
③在show.blade.php里面引用该组件user_vote_button：
    
    <user_vote_button answer="{{ $answer->id }}"></user_vote_button>
④在api路由文件中创建两条路由分别为：
    
    Route::middleware('auth:api')->get('/answer/{id}/votes/','VotersController@index');
    Route::middleware('auth:api')->post('/answer/vote','VotersController@voted');
⑤创建一个控制器名为：VotersController：
    
    php artisan make:controller VotersController
    内容如下：
    <?php
    
    namespace App\Http\Controllers;
    
    use App\Answer;
    use Illuminate\Http\Request;
    
    class VotersController extends Controller
    {
        public function index($id)
        {
            $count = Answer::find($id)->users()->count();
            $votes = \Auth::guard('api')->user()->votes()->pluck('answer_id')->toArray();
            if(in_array($id,$votes)){
                return response()->json(['followed'=>true,'count'=>$count]);
            }
            return response()->json(['followed'=>false,'count'=>$count]);
        }
    
        public function voted(Request $request)
        {
           $vote = \Auth::guard('api')->user()->vote($request->get('answer'));
            if(count($vote['attached']) > 0){
                return response()->json(['followed'=>true]);
            }
            return response()->json(['followed'=>false]);
        }
    }
## 步骤十五、实现发送私信功能：
### 1、实现发送私信功能前期表的准备工作：
①创建一个名为Message 的model，并生成迁移文件：
    
    php artisan make:model Message -m
②create_messages_table迁移文件内容：即私信表的字段：
    
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_user_id');//发送私信的人的id
            $table->unsignedInteger('to_user_id');//接收私信的人的id
            $table->text('body');//存储私信的具体内容
            $table->string('has_read',8)->default('F');//默认未读
            $table->timestamp('read_at')->nullable();//记录读取这条私信的时间,这是可以为空的
            $table->timestamps();
        });
    }
③Message model文件内容为：
    
    class Message extends Model
    {
        protected $fillable = ['from_user_id','to_user_id','body'];
    
        public function fromUser()//定义私信与发送私信用户的关系
        {
            return $this->belongsTo(User::class,from_user_id);
        }
    
        public function toUser()//定义私信与接收私信用户的关系
        {
            return $this->belongsTo(User::class,to_user_id);
        }
    }
④到User model定义users表与messages表的关系：
    
    public function messages()//这里注意，外键要定义为‘to_user_id’
    {
        return $this->hasMany(Message::class,'to_user_id');
    }
### 2、实现发送私信功能发送界面准备工作（使用模态框）：
①在resources/assets/js/components目录下面创建名为：SendMessage.vue文件，内容为：
    
    <template>
        <div>
            <button class="btn btn-default pull-right"
                    data-toggle="modal" data-target="#myModal"
            >
                发送私信
            </button>
            <!-- 模态框（Modal） -->
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                &times;
                            </button>
                            <h4 class="modal-title" id="myModalLabel">
                                发送私信给：{{ user_name }}
                            </h4>
                        </div>
                        <div class="modal-body">
                            <textarea class="form-control" v-model="body" v-if="!status"></textarea>
                            <div class="alert alert-success" v-if="status">私信发送成功</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                            <button type="submit" class="btn btn-primary" @click="store">
                                发送私信
                            </button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal -->
            </div>
        </div>
    </template>
    
    <script>
        export default {
            props:['user_name','user_id'],
            data() {
                return {
                    body:'',
                    status: false
                }
            },
            methods:{
                store() {
                    axios.post('/api/message/store',{'body':this.body,'user_id':this.user_id}).then(function (response) {
                        console.log(response.data.status)
                        this.status = response.data.status
                        setTimeout(function(){//设置显示多久才消失
                            $('#myModal').modal('hide')
                        },1000)
                    }.bind(this))//这里一定要绑定this，不然会报错，识别不到status。
                }
            }
        }
    </script>
②在app.js里面注册SendMessage.vue组件，添加：

    Vue.component('send_message', require('./components/SendMessage.vue'));
③到show.blade.php文件相应位置（发送私信的按钮地方）替换代码为：
    
    <send_message user_name="{{ $question->user->name }}" user_id="{{ $question->user->id }}"></send_message>
④执行npm run dev编译
⑤在api路由文件创建一个路由用于保存私信到数据库：
    
    Route::middleware('auth:api')->post('/message/store','MessagesController@store');
⑥创建MessagesController控制器：
    
    php artisan make：controller MessagesController
    内容为：
    public function store(Request $request)
    {
        $message = Message::create([
            'from_user_id'=> \Auth::guard('api')->user()->id,
            'to_user_id'  => request('user_id'),
            'body'        => request('body')，
           
        ]);
        if($message){
            return response()->json(['status'=> true]);
        }
        return response()->json(['status'=> false]);
    }
### 3、私信列表（即创建一个用户查看私信的界面）：
#### **如何将diffForHuman的英文改为中文方法：
    在app/Providers/AppServiceProvider.php里面的boot()方法里面添加如下代码：
    public function boot()
    {
        Carbon::setLocale('zh');
    }
#### **特别注意在做这一步时，需要为messages表添加一个字段dialog_id，作为发送者与接受者的会话id。
    
    执行：php artisan make:migration add_dialog_id_to_messages --table=messages
    内容为：
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->bigInteger('dialog_id')->notnull();//表示回话的id
        });
    }
    
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('dialog_id');
        });
    }
    执行：php artisan migrate
#### **到这里还需要特别注意一点：需要修改MessagesController里面的store方法，需要在发送私信的时候判断是否已经有过对话并保持回话id即dialog_id:
    
    <?php
    
    namespace App\Http\Controllers;
    
    use App\Message;
    use Illuminate\Http\Request;
    
    class MessagesController extends Controller
    {
        public function store(Request $request)
        {
            $hasDialog = Message::where('from_user_id',\Auth::guard('api')->user()->id)->where('to_user_id',request('user_id'))
                                  ->orWhere('to_user_id',\Auth::guard('api')->user()->id)->where('from_user_id',request('user_id'))
                                  ->first();//判断两个用户之间是否发送过私信，即是否产生过对话。
            $message = Message::create([
                'from_user_id'=> \Auth::guard('api')->user()->id,
                'to_user_id'  => request('user_id'),
                'body'        => request('body'),
                'dialog_id'   => $hasDialog ? $hasDialog->dialog_id : time().\Auth::guard('api')->user()->id//如果产生过对话就用原来的对话id，如果没有产生就生成新的对话id。
            ]);
            if($message){
                return response()->json(['status'=> true]);
            }
            return response()->json(['status'=> false]);
        }
    }

#### 上面的准备工作做好之后就可以进行以下步骤了：
①创建一个Inbox的controller：
    
    php artisan make:controller InboxController
    内容为：
    <?php
    
    namespace App\Http\Controllers;
    
    use App\Message;
    use Illuminate\Http\Request;
    
    class InboxController extends Controller
    {
    
        /**
         * InboxController constructor.
         */
        public function __construct()
        {
            $this->middleware('auth');
        }
    
        public function index()
        {
            $messages= Message::where('from_user_id',\Auth::id())->orWhere('to_user_id',\Auth::id())->with(['fromUser','toUser'])->get()->groupBy('dialog_id');
    
            return view('inbox.index',['messages' => $messages]);
        }
    
        public function show($dialog_id)
        {
            $messages = Message::where('dialog_id',$dialog_id)->latest()->get();
            return view('inbox.show',compact('messages','dialog_id'));
        }
    }


②在web路由文件里创建一条路由：
    
    Route::get('/inbox', 'InboxController@index');
    Route::get('/inbox/{dialog_id}', 'InboxController@show');
③在resources/views/inbox里面创建一个index.blade.php视图文件：
    
    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">私信列表</div>
    {{--下面是显示私信的内容--}}
                        <div class="panel-body">
                            @foreach($messages as $messageGroup)
                            <div class="media">
                                <div class="media-left">
                                    <a href="">
                                        @if(Auth::id() == $messageGroup->last()->to_user_id)
                                        <img style="border-radius:50%" src="{{ $messageGroup->last()->fromUser->avatar }}" alt="{{$messageGroup->last()->fromUser->name}}">
                                        @else
                                        <img style="border-radius:50%" src="{{ $messageGroup->last()->toUser->avatar }}" alt="{{$messageGroup->last()->toUser->name}}">
                                        @endif
                                    </a>
                                </div>
                                <div class="media-body">
                                        <span class="media-heading">
                                            @if(Auth::id() == $messageGroup->last()->to_user_id)
                                            <a href="">{{$messageGroup->last()->fromUser->name}}</a>   {{ $messageGroup->last()->created_at->diffForHumans() }}
                                            @else
                                            <a href="">{{$messageGroup->last()->toUser->name}}</a>   {{ $messageGroup->last()->created_at->diffForHumans() }}
                                            @endif
                                        </span>
                                    <div>
                                        <a href="/inbox/{{$messageGroup->last()->dialog_id}}">
                                            {!!  $messageGroup->last()->body  !!}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
    {{--上面是显示私信的内容--}}
                    </div>
                </div>
            </div>
        </div>
    @endsection
④在resources/views/inbox里面创建一个show.blade.php视图文件：
    
    @extends('layouts.app')
    
    @section('content')
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">私信内容</div>
                        {{--下面是显示私信的内容--}}
                        <div class="panel-body">
                            回复私信位置....
                            @foreach($messages as $key => $message)
                                <div class="media">
                                    <div class="media-left">
                                        <a href="">
                                            <img style="border-radius:50%" src="{{ $message->fromUser->avatar }}" alt="{{$message->fromUser->name}}">
                                        </a>
                                    </div>
                                    <div class="media-body">
                                        <span class="media-heading">
                                            <a href="">{{$message->fromUser->name}}</a>   {{ $message->created_at->diffForHumans() }}
                                        </span>
                                        <div>
                                            {!!  $message->body  !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{--上面是显示私信的内容--}}
                    </div>
                </div>
            </div>
        </div>
    @endsection
### 4、回复私信：
①在resources/views/inbox/show.blade.php视图文件里面的‘回复私信位置....’的地方插入一个回复私信表单：
    
    <form class="form-group" action="/inbox/{{ $dialog_id }}/store" method="post">
        {{csrf_field()}}
        <textarea class="form-control" name="body"></textarea>
        <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px">发送私信</button>
    </form>
②在web路由文件创建一条路由用于发送私信：
    
    Route::post('/inbox/{dialog_id}/store', 'InboxController@store');
③在InboxController里面添加Send方法：
    
    public function store($dialog_id)
    {
        $message = Message::where('dialog_id',$dialog_id)->first();
        $toUserId = ($message->to_user_id == \Auth::id()) ? $message->from_user_id : $message->to_user_id;
        Message::create([
            'from_user_id' => \Auth::id(),
            'to_user_id'   => $toUserId,
            'body'         => request('body'),
            'dialog_id'    => $dialog_id
        ]);
        return back();
    }
### 5、标记私信已读：
①在InboxController里面的show()方法，添加如下代码：
    
    $messages->markAsRead();//标记已读
    变化之后代码如下：
    public function show($dialog_id)
    {
        $messages = Message::where('dialog_id',$dialog_id)->latest()->get();
        $messages->markAsRead();//标记已读
        return view('inbox.show',compact('messages','dialog_id'));
    }
②到Message model文件中定义markAsRead()这个方法：
    
    public function markAsRead()
    {
        if(is_null($this->read_at)){//如果这条私信没有读
            return $this->forceFill(['has_read' => 'T','read_at' => $this->freshTimestamp()])->save();//填充字段，标记已读
        }
    }    
**③到这里如果去访问inbox页面会报错：Method markAsRead does not exist.这是因为在第①步里面show方法得到的$message是一个collection对象，但我们没有在collection里面定义markAsRead这个方法
但是我们又不希望去修改collection的源码，所以这里就给出了一个方法:**
    
    1、到Message model里面添加一个newCollection()方法,代码如下：
    public function newCollection(array $models=[])
    {
        return new MessageCollection($models);//这里的这个MessageCollection类还没有写好，需要新建一个类
    }
    2、在app目录下面新建一个MessageCollection类，代码为：
    <?php
    
    namespace App;
    
    
    use Illuminate\Database\Eloquent\Collection;
    
    class MessageCollection extends Collection
    {
        public function markAsRead()//注意，这里的方法必须要与Message model里面的方法名字一样
        {
            $this->each(function ($message){
                $message->markAsRead();//这个方法就是在Message model里面定义的方法，就可以实现对每一条message执行这个方法了
            });
        }
    }
④之后进入show页面的时候，就会自动将显示的信息都标记为已读了，但是这里有个不好的地方，只要一个人读了，就算另外一个人没读，但是也是显示已读了，
所以需要在MessageCollection的markAsRead()方法添加一个条件判断即可，变化后的代码如下：

    <?php
    
    namespace App;
    
    
    use Illuminate\Database\Eloquent\Collection;
    
    class MessageCollection extends Collection
    {
        public function markAsRead()//注意，这里的方法必须要与Message model里面的方法名字一样
        {
            $this->each(function ($message){
                if($message->to_user_id === \Auth::id()){//表示只有私信的接收者读了才能标记已读。
                    $message->markAsRead();//这个方法就是在Message model里面定义的方法，就可以实现对每一条message执行这个方法了
                }
            });
        }
    }
### 6、标记未读，即给未读的私信添加样式好辨认：
①在Message  model 里面添加方法如下：
    
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
②在inbox/index.blade.php里面私信列表添加样式如下：
    
    style="{{$messageGroup->last()->shouldAddUnreadClass() ? 'background-color: #b9a791' : '' }}"
### 7、实现发送私信通知：
①创建一个新的Notification：
    
    php artisan make:notification newMessageNotification
②newMessageNotification具体内容为：

    <?php
    
    namespace App\Notifications;
    
    use App\Message;
    use Illuminate\Bus\Queueable;
    use Illuminate\Notifications\Notification;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Messages\MailMessage;
    
    class newMessageNotification extends Notification
    {
        use Queueable;
        protected $message;
        public function __construct(Message $message)
        {
            $this->message = $message;
        }
        public function via($notifiable)
        {
            return ['database'];
        }
    
        public function toDatabase($notifiable)
        {
            return [
                'name' => $this->message->fromUser->name,
                'dialog_id' => $this->message->dialog_id
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
③修改InboxController里面的store方法，即添加发送Notification,修改后代码如下:
    
    public function store($dialog_id)
    {
        $message = Message::where('dialog_id',$dialog_id)->first();
        $toUserId = ($message->to_user_id == \Auth::id()) ? $message->from_user_id : $message->to_user_id;
        $newMessage = Message::create([
            'from_user_id' => \Auth::id(),
            'to_user_id'   => $toUserId,
            'body'         => request('body'),
            'dialog_id'    => $dialog_id
        ]);
        $newMessage->toUser->notify(new newMessageNotification($newMessage));
        return back();
    }
④在views/notifications/里面创建一个名为：new_message_notification.blade.php的视图内容入下：
    
    <li class="notifications">
        <a href="/inbox/{{$notification->data['dialog_id']}}">
            {{ $notification->data['name'] }} 给你发了一条私信
        </a>
    </li>
### 8、实现私信通知Notification标记已读：
①添加未读样式，修改views/notifications/new_message_notification.blade.php为：
    
    @if($notification->unread())  //unread()这个方法是在DatabaseNotification里面的
        <li class="notifications" style="background-color: yellowgreen">
    @else
        <li class="notifications">
    @endif
        <a href="/inbox/{{$notification->data['dialog_id']}}">
            {{ $notification->data['name'] }} 给你发了一条私信
        </a>
    </li>
②点击链接标记已读然后跳转到具体的私信列表:
1.同样是修改views/notifications/new_message_notification.blade.php为：
    
    @if($notification->unread())
        <li class="notifications" style="background-color: yellowgreen">
    @else
        <li class="notifications">
    @endif
        <a href="/notifications/{{ $notification->id }}?redirect_url=inbox/{{$notification->data['dialog_id']}}">//这里是需要注意的的地方
            {{ $notification->data['name'] }} 给你发了一条私信
        </a>
    </li>
2.到web路由文件添加一条路由实现跳转：
    
    Route::get('/notifications/{notification}', 'NotificationsController@show');
3.进入到NotificationsController，添加show方法：
        
    public function show(DatabaseNotification $notification)//这里需要注意引入DatabaseNotification.
    {
        $notification->markAsRead();

        return redirect(request('redirect_url'));
    }

    
    
    
    