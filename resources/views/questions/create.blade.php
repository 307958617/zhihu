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
                            <div class="form-group">
                                <label for="title">问题名称</label>
                                <input type="text" class="form-control" name="title" placeholder="请输入问题名称">
                            </div>
                            <div class="form-group">
                                <label for="title">问题内容</label>
                                <!-- 编辑器容器 -->
                                <script id="container" name="body" type="text/plain"></script>
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
        var ue = UE.getEditor('container');
        ue.ready(function() {
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>
@endsection