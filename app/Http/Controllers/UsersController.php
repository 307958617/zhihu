<?php

namespace App\Http\Controllers;

use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function avatar()
    {
        return view('users.avatar');
    }

    public function changeAvatar(Request $request)
    {
        $file = $request->file('img');  //传入图片的文件
        $filename = md5(str_random(16).\Auth::id()).'.'.$file->getClientOriginalExtension();//设置图片的名称
        $file->move(public_path('avatars'),$filename);//移动图片到保存的相应位置

        \Auth::user()->avatar = '/avatars/'.$filename;//保存图片地址到数据库
        \Auth::user()->save();
        return ['url' => \Auth::user()->avatar ];
    }
}
