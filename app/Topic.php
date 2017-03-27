<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = ['name','bio','questions_count','follower_count'];

    public function questions()//定义与questions表的多对多关系
    {
        return $this->belongsToMany(Question::class)->withTimestamps();
    }
}
