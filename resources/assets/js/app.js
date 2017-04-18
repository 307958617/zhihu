
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example', require('./components/Example.vue'));
Vue.component('question_follow_button', require('./components/QuestionFollowButton.vue'));
Vue.component('user_follow_button', require('./components/UserFollowButton.vue'));
Vue.component('user_vote_button', require('./components/UserVoteButton.vue'));
Vue.component('send_message', require('./components/SendMessage.vue'));
Vue.component('user-avatar', require('./components/Avatar.vue'));

const app = new Vue({
    el: '#app'
});
