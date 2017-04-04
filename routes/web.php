<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::get('email/verify/{token}',['as' => 'verify.email','uses' => 'EmailController@verify']);

Route::resource('questions','QuestionsController',['names'=>[  //命名路由
    'create' => 'questions.create',// 用于显示提交问题的表单的页面
    'show' => 'questions.show'  //用于发布问题后显示问题的页面
]]);

Route::post('questions/{question}/answers/store',['as'=>'answers.store','uses'=>'AnswersController@store']);

Route::get('questions/{question}/follow','QuestionFollowController@follow');

Route::get('/notifications', 'NotificationsController@index');