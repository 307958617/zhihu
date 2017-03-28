<?php

namespace App\Repositories;


use App\Question;
use App\Topic;

class QuestionRepository
{
    public function findQuestionById_withTopics($id)
    {
        return Question::where('id',$id)->with('topics')->first();
    }

    public function createQuestion(array $data)
    {
        return Question::create($data);
    }

    public function normalizeTopics(array $topics)
    {
        return collect($topics)->map(function ($topic){ //通过collect()和map()方法遍历数组
            if (is_numeric($topic)){   //如果是数字就返回数字
                Topic::find($topic)->increment('questions_count');//将话题关联的问题数量+1
                return (int)$topic;
            }
            $newTopic = Topic::create(['name'=>$topic,'questions_count'=>1]);//如果不是数字就表示没有这个话题因此新建一个topic，将问题数设置为1
            return $newTopic->id;//返回这个topic的id值
        })->toArray();//转换成数组
    }
}