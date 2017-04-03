<template>
    <button class="btn btn-default"
            :class="{'btn-success':followed}"
            v-text="text"
            @click="follow"
    >
    </button>
</template>

<script>
    export default {
        props:['question','user'],//这里的数据就是从show.blade.php视图里面传递进来的两个值
        data() {
            return {
                followed:false
            }
        },
        mounted() {
            axios.post('http://zhihu/api/question/follower',{'q':this.question,'u':this.user}).then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                this.followed = response.data.followed;
                console.log(response.data.followed)
            }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
        },
        computed: { //计算属性
            text() {
                return this.followed ? '取消关注':'关注该问题'
            }
        },
        methods:{
            follow() {
                axios.post('http://zhihu/api/question/follow',{'q':this.question,'u':this.user}).then(function (response) { //注意两个地方：1、这里不能用this.axios.get()；2、传递的数据需要用[]包住，可以用{'q':this.question,'u':this.user}，也可以直接传数据[this.question,this.user],只是后一种方法在api里面不好指定，需要用数组来选择。
                    this.followed = response.data.followed;
                    console.log(response.data.followed)
                }.bind(this));  //注意这里需要用到.bind(this)不然要报错，找不到followed
            }
        }
    }
</script>
