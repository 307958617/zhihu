@extends('layouts.app')

@section('content')
    @include('vendor.ueditor.assets') {{--这行的作用是引入编辑器需要的 css,js 等文件，所以你不需要再手动去引入它们--}}
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">发布问题</div>
                    <div class="panel-body">
                        <form action="/questions" method="post" role="form">
                            {{csrf_field()}}
                            <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">{{--这是为了显示错误时的样式，显示为红色--}}
                                <label for="title">问题名称</label>
                                <input type="text" class="form-control" name="title" value="{{ old('title') }}" placeholder="请输入问题名称">{{--这是为了保留原来提交的数据，避免用户重填--}}
                                @if ($errors->has('title'))    {{--这段就是为了显示相应的提示信息--}}
                                <span class="help-block">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group{{ $errors->has('body') ? ' has-error' : '' }}">
                                <label for="body">问题内容</label>
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
                            <button type="submit" class="btn btn-primary pull-right">发布问题</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container',{
            toolbars: [
                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft','justifycenter', 'justifyright',  'link', 'insertimage','print', 'fullscreen']
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