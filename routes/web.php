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

Route::get('email/verify/{token}',['as' => 'verify.email','uses' => 'EmailController@verify']);

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::resource('questions','QuestionsController',['names'=>[
    'create' => 'questions.create',//   命名路由
    'show' => 'questions.show'
]]);
