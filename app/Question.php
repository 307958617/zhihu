<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['title','body','user_id'];

    public function topics()
    {
        return $this->belongsToMany(Topic::class)->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function scopePublished($query)//定义发布限制条件，注意名称的写法 小写的scope+第一个字母大写的Published，采用驼峰法。
    {
        return $query->where('is_hidden','F');
    }
}
