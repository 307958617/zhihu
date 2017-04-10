@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">私信内容</div>
                    {{--下面是显示私信的内容--}}
                    <div class="panel-body">
                        <form class="form-group" action="/inbox/{{ $dialog_id }}/store" method="post">
                            {{csrf_field()}}
                            <textarea class="form-control" name="body"></textarea>
                            <button type="submit" class="btn btn-success pull-right" style="margin-top: 10px">发送私信</button>
                        </form>
                        <div style="margin-top: 50px">
                            @foreach($messages as $key => $message)
                                <div class="media">
                                    <div class="media-left">
                                        <a href="">
                                            <img style="border-radius:50%" src="{{ $message->fromUser->avatar }}" alt="{{$message->fromUser->name}}">
                                        </a>
                                    </div>
                                    <div class="media-body">
                                    <span class="media-heading">
                                        <a href="">{{$message->fromUser->name}}</a>   {{ $message->created_at->diffForHumans() }}
                                    </span>
                                        <div>
                                            {!!  $message->body  !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{--上面是显示私信的内容--}}
                </div>
            </div>
        </div>
    </div>
@endsection
