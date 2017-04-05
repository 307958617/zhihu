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
