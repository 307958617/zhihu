@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $question->title }}
                        @foreach($question->topics as $topic)
                            <div class="badge">{{ $topic->name }}</div>
                        @endforeach
                    </div>

                    <div class="panel-body">
                        {!! $question->body !!}
                    </div>
                    <div class="action">
                        @if(Auth::check() && Auth::user()->owns($question)) {{--这是判断权限的如只有登录并且是这个问题的发起者成能删除它--}}
                            <form action="/questions/{{$question->id}}" method="post">
                                {{method_field('DELETE')}}
                                {{csrf_field()}}
                                <button class="btn" style="background:transparent;color: red">删除</button> {{--transparent这是一个css样式,背景透明--}}
                            </form>
                        @endif
                    </div>
                </div>
{{--下面是实现问题答案提交功能代码--}}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        共有 {{ $question->answers_count }} 个答案
                    </div>

                    <div class="panel-body">
{{--下面是显示问题答案的代码--}}
                        @foreach($question->answers as $answer)
                            <div class="media">
                                <div class="media-left">
                                    <a href="">
                                        <img style="border-radius:50%" src="{{$answer->user->avatar}}" alt="{{$answer->user->name}}">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <span class="media-heading">
                                        <a href="">{{$answer->user->name}}</a>
                                    </span>
                                    {!!  $answer->body  !!}
                                </div>
                            </div>
                        @endforeach
{{--上面是显示问题答案的代码--}}
                        <div class="action">
                            @if(Auth::check()) {{--这是判断权限的如只有登录的用户才能回答问题添加答案--}}
                            <form action="/questions/{{$question->id}}/answers/store" method="post"> {{--注意提交的地址--}}
                                {{csrf_field()}}
                                <div class="form-group{{ $errors->has('body') ? ' has-error' : '' }}">
                                    <!-- 编辑器容器 -->
                                    <script id="container" name="body" type="text/plain">
                                        {{ old('body') }}  {{--这里要注意，不是放在里面，而是用放到标签之间--}}
                                    </script>
                                    @if ($errors->has('body'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('body') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <button class="btn btn-primary pull-right">发布答案</button> {{--transparent这是一个css样式,背景透明--}}
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
{{--上面是实现问题答案提交功能代码--}}
            </div>
{{--下面是关注问题模块--}}
            <div class="col-md-3">
                <div class="panel panel-default" style="text-align: center">
                    <div class="panel-heading">
                        <h1>{{ $followersCount }}</h1>
                        <span>关注者</span>
                    </div>
                    @if(Auth::check())
                    <div class="panel-body">
                        {{--<a href="/questions/{{ $question->id }}/follow" class="btn btn-default {{Auth::user()->followed($question->id)?'btn-success':''}}">--}}
                            {{--{{Auth::user()->followed($question->id)?'取消关注':'关注该问题'}}--}}
                        {{--</a>--}}
                        <question_follow_button question="{{ $question->id }}" user="{{ Auth::id() }}"></question_follow_button>
                        <a href="#editor" class="btn btn-primary">撰写答案</a>
                    </div>
                    @endif
                </div>
            </div>
{{--上面是关注问题模块--}}
{{--下面是关注用户模块--}}
            <div class="col-md-3">
                <div class="panel panel-default" style="text-align: center">
                    <div class="panel-heading">
                        <h5>关于作者</h5>
                    </div>
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-left">
                                <a href="">
                                    <img style="border-radius:50%" src="{{$question->user->avatar}}" alt="{{$question->user->name}}">
                                </a>
                            </div>
                            <div class="media-body">
                                <h5 class="media-heading">
                                    <a href="">{{$question->user->name}}</a>
                                </h5>
                            </div>
                        </div>
                        <div class="user-status" style="display: flex;margin-top: 20px">
                            <div class="status-item" style="padding: 2px 20px;">
                                <div class="status-text">问题</div>
                                <div class="status-count">{{$question->user->questions_count}}</div>
                            </div>
                            <div class="status-item" style="padding: 2px 20px;">
                                <div class="status-text">回答</div>
                                <div class="status-count">{{$question->user->answers_count}}</div>
                            </div>
                            <div class="status-item" style="padding: 2px 20px;">
                                <div class="status-text">关注者</div>
                                <div class="status-count">{{$question->user->followers_count}}</div>
                            </div>
                        </div>
                        <question_follow_button question="{{ $question->id }}" user="{{ Auth::id() }}"></question_follow_button>
                        <a href="#editor" class="btn btn-primary">发送私信</a>
                    </div>
                </div>
            </div>
{{--上面是关注用户模块--}}
        </div>
    </div>
@endsection
{{--下面是实现问题答案提交功能的富文本框依赖程序--}}
@section('js')
    @include('vendor.ueditor.assets')
    <script type="text/javascript">
        var ue = UE.getEditor('container',{
            toolbars: [
                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage', 'fullscreen']
            ],
            elementPathEnabled: false,
            enableContextMenu: false,
            autoClearEmptyNode:true,
            wordCount:false,
            imagePopup:false,
            autotypeset:{ indent: true,imageBlockLine: 'center' }
        });
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>
@endsection
{{--上面是实现问题答案提交功能的富文本框依赖程序--}}