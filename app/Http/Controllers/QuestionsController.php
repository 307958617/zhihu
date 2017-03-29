<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Repositories\QuestionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->middleware('auth')->except('index','show');//表示除了index和show展示页面不需要登录，其他需要登录才行
        $this->questionRepository = $questionRepository;//依赖注入QuestionRepository
    }

    public function index()
    {
        $questions = $this->questionRepository->getAllQuestions();
        return view('questions.index',compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('questions.create');//返回发布问题的视图
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuestionRequest $request)
    {
        $topics = $this->questionRepository->normalizeTopics($request->topics);//这里的topics就是select2传递过来的数组数据
        $data = [
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'user_id' => Auth::id()  //表示是谁发布的问题
        ];
        $question = $this->questionRepository->createQuestion($data);//保持到数据库中
        $question->topics()->attach($topics);//将关联关系写入中间表
        return redirect(route('questions.show',[$question->id]));//跳转到问题显示页面，[]里面的内容是这个文章的ID
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $question = $this->questionRepository->findQuestionById_withTopics($id);
        return view('questions.show',compact('question'));//传递到视图
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $question = $this->questionRepository->findQuestionById_withTopics($id);
        if(Auth::user()->owns($question)){         //判断用户是否是文章的发布者，如果是才能看到编辑界面，否则就跳转回去。这里的owns()方法需要到User model里面添加
            return view('questions.edit',compact('question'));
        }
        flash('对不起，你不是作者不能编辑该文章！');
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreQuestionRequest $request, $id)//和store方法的验证规则一样
    {
        $topics = $this->questionRepository->normalizeTopics($request->topics);
        $question = $this->questionRepository->findQuestionById_withTopics($id);
        $question->update([
            'title'=>$request->get('title'),
            'body' =>$request->get('body')
        ]);

        $question->topics()->sync($topics);//将关联关系写入中间表，注意：这里需要将attach方法改为sync方法，同步修改。
        return redirect(route('questions.show',[$question->id]));//跳转到问题显示页面，[]里面的内容是这个文章的ID
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->questionRepository->delQuestionById($id);
    }
}
