<template>
    <div>
        <my-upload field="img"
                   @crop-success="cropSuccess"
                   @crop-upload-success="cropUploadSuccess"
                   @crop-upload-fail="cropUploadFail"
                   v-model="show"
                   :width="300"
                   :height="300"
                   url="/avatar"
                   :params="params"
                   :headers="headers"
                   img-format="png"></my-upload>
        <img style="border-radius: 50%;height: 50px;" :src="imgDataUrl">
        <a class="btn" @click="toggleShow">修改头像</a>
    </div>
</template>

<script>
    import 'babel-polyfill'; // es6 shim
    import myUpload from 'vue-image-crop-upload/upload-2.vue';

    export default {
        props:['avatar'],//这里的avatar是用户的头像地址与avatar.blade.php里面的是一个
        data(){
          return {
              show: false,
              params: {
                  _token: Laravel.csrfToken,
                  name: 'img'
              },
              headers: {
                  smail: '*_~'
              },
              imgDataUrl: this.avatar // 这里就是头像图片的地址，即用props传递进来的地址
          }
        },
        components: {
            'my-upload': myUpload
        },
        methods: {
            toggleShow() {
                this.show = !this.show;
            },

            cropSuccess(imgDataUrl, field){
                console.log('-------- crop success --------');
                this.imgDataUrl = imgDataUrl;
            },
            /**
             * upload success
             *
             * [param] jsonData   服务器返回数据，已进行json转码
             * [param] field
             */
            cropUploadSuccess(jsonData, field){
                console.log('-------- upload success --------');
                console.log(jsonData);
                console.log('field: ' + field);
                this.imgDataUrl = jsonData.url;
                this.toggleShow();
            },
            /**
             * upload fail
             *
             * [param] status    server api return error status, like 500
             * [param] field
             */
            cropUploadFail(status, field){
                console.log('-------- upload fail --------');
                console.log(status);
                console.log('field: ' + field);
            }
        }
    }
</script>
