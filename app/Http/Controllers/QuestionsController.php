<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Question;
use App\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth')->except('index','show');//表示除了index和show展示页面不需要登录，其他需要登录才行
    }

    public function index()
    {
        //
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
        $topics = $this->normalizeTopics($request->topics);//这里的topics就是select2传递过来的数组数据
        $date = [
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'user_id' => Auth::id()  //表示是谁发布的问题
        ];
        $question = Question::create($date);//保持到数据库中
        $question->topics()->attach($topics);//将关联关系写入中间表
        return redirect(route('questions.show',[$question->id]));//跳转到问题显示页面，[]里面的内容是这个文章的ID
    }

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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $question = Question::find($id);
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
