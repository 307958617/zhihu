@if($notification->unread())
    <li class="notifications" style="background-color: yellowgreen">
@else
    <li class="notifications">
@endif
    <a href="/notifications/{{ $notification->id }}?redirect_url=inbox/{{$notification->data['dialog_id']}}">
        {{ $notification->data['name'] }} 给你发了一条私信
    </a>
</li>