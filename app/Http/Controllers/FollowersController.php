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
    {
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
