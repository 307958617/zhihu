<template>
    <div>
        <button class="btn btn-default pull-right"
                data-toggle="modal" data-target="#myModal"
        >
            发送私信
        </button>
        <!-- 模态框（Modal） -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            &times;
                        </button>
                        <h4 class="modal-title" id="myModalLabel">
                            发送私信给：{{ user_name }}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" v-model="body" v-if="!status"></textarea>
                        <div class="alert alert-success" v-if="status">私信发送成功</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                        <button type="submit" class="btn btn-primary" @click="store">
                            发送私信
                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal -->
        </div>
    </div>
</template>

<script>
    export default {
        props:['user_name','user_id'],
        data() {
            return {
                body:'',
                status: false
            }
        },
        methods:{
            store() {
                axios.post('/api/message/store',{'body':this.body,'user_id':this.user_id}).then(function (response) {
                    console.log(response.data.status)
                    this.status = response.data.status
                    setTimeout(function(){//设置显示多久才消失
                        $('#myModal').modal('hide')
                    },1000)
                }.bind(this))//这里一定要绑定this，不然会报错，识别不到status。
            }
        }
    }
</script>
