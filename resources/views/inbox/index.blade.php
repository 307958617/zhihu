@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">私信列表</div>
{{--下面是显示私信的内容--}}
                    <div class="panel-body">
                        @foreach($messages as $messageGroup)
                        <div class="media">
                            <div class="media-left">
                                <a href="">
                                    @if(Auth::id() == $messageGroup->last()->to_user_id)
                                    <img style="border-radius:50%" src="{{ $messageGroup->last()->fromUser->avatar }}" alt="{{$messageGroup->last()->fromUser->name}}">
                                    @else
                                    <img style="border-radius:50%" src="{{ $messageGroup->last()->toUser->avatar }}" alt="{{$messageGroup->last()->toUser->name}}">
                                    @endif
                                </a>
                            </div>
                            <div class="media-body">
                                    <span class="media-heading">
                                        @if(Auth::id() == $messageGroup->last()->to_user_id)
                                        <a href="">{{$messageGroup->last()->fromUser->name}}</a>   {{ $messageGroup->last()->created_at->diffForHumans() }}
                                        @else
                                        <a href="">{{$messageGroup->last()->toUser->name}}</a>   {{ $messageGroup->last()->created_at->diffForHumans() }}
                                        @endif
                                    </span>
                                <div>
                                    <a href="/inbox/{{$messageGroup->last()->dialog_id}}">
                                        {!!  $messageGroup->last()->body  !!}
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
