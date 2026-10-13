@component('mail::message')
    # Official Response

    {{ $replyMessage }}

    @component('mail::panel')
        **Your Original Message:** {{ $originalMessage }}
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }} Team
@endcomponent
