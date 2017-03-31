<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $table = 'question_user';

    protected $fillable = ['question_id','user_id'];
}
