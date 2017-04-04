<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('api')->get('/topics', function (Request $request) {
    $topics = \App\Topic::where('name','like','%'.$request->query('q').'%')
                                               ->pluck('id','name');
    return response()->json(['items'=>$topics]);
});

Route::middleware('auth:api')->post('question/follower', function (Request $request) {
    $followed = \App\Follow::where('question_id',$request->get('q'))->where('user_id',$request->get('u'))->count();
    if($followed > 0)
        return response()->json(['followed'=> true]);
    else
        return response()->json(['followed'=> false]);
});

Route::middleware('auth:api')->post('question/follow', function (Request $request) {
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

Route::middleware('auth:api')->get('/user/followers/{id}','FollowersController@index');
Route::middleware('auth:api')->post('/user/follow','FollowersController@follow');