@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
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
            </div>
        </div>
    </div>
@endsection