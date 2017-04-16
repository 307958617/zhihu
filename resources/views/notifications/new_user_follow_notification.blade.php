@if($notification->unread())
    <li class="notifications" style="background-color: yellowgreen">
@else
    <li class="notifications">
@endif
    {{ $notification->data['name'] }} 关注了你！
</li>