@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">私信列表</div>
{{--下面是显示私信的内容--}}
                    <div class="panel-body">
                        @foreach($messages as $key => $message)
                        <div class="media">
                            <div class="media-left">
                                <a href="">
                                    @if(Auth::id() == $key)
                                    <img style="border-radius:50%" src="{{ $message->first()->fromUser->avatar }}" alt="{{$message->first()->fromUser->name}}">
                                    @else
                                    <img style="border-radius:50%" src="{{ $message->first()->toUser->avatar }}" alt="{{$message->first()->toUser->name}}">
                                    @endif
                                </a>
                            </div>
                            <div class="media-body">
                                    <span class="media-heading">
                                        @if(Auth::id() == $key)
                                        <a href="">{{$message->first()->fromUser->name}}</a>   {{ $message->first()->created_at->diffForHumans() }}
                                        @else
                                        <a href="">{{$message->first()->toUser->name}}</a>   {{ $message->first()->created_at->diffForHumans() }}
                                        @endif
                                    </span>
                                <div>
                                    @if(Auth::id() == $message->first()->toUser->id)
                                        <a href="/inbox/{{Auth::id()}}/{{$message->first()->fromUser->id}}">
                                    @else
                                        <a href="/inbox/{{Auth::id()}}/{{$message->first()->toUser->id}}">
                                    @endif
                                        {!!  $message->first()->body  !!}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
{{--上面是显示私信的内容--}}
                </div>
            </div>
        </div>
    </div>
@endsection
