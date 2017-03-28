@extends('layouts.app')

@section('content')
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
                            <div class="div form-group{{ $errors->has('topic') ? ' has-error' : '' }}">
                                <label for="topic">选择话题</label>
                                <select name="topics[]" id="topic" class="form-control" multiple="multiple">
                                </select>
                                @if ($errors->has('topic'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('topic') }}</strong>
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
@endsection

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
        //下面是select2的引入
        $(document).ready(function () {
            $('#topic').select2({
                placeholder:'select a topic',
                tags:true,//表示可以自己添加输入的值
                minimumInputLength: 1,
                ajax:{
                    url:'/api/topics',//api的路径
                    dataType:'json',
                    delay:250,
                    data:function (params) {
                        return {
                            q:params.term   //q代表传递到api的参数值，与$request->query('q')中的q对应
                        }
                    },
                    processResults:function (data) {
                        return {
                            results:$.map(data.items,function (id,name) {//data.items就是api查询后传递回来的json数据即response()->json(['items'=>$topics])
                                return {id:id,text:name};                 //这里的$.map是遍历传过来的数据好显示到select2选择框
                            })
                        };
                    }
                }
            });
        });
    </script>
@endsection
