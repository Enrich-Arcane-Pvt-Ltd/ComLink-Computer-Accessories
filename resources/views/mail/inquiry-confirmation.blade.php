@component('mail::message')
# Inquiry Received Successfully!

Dear {{ $slot }},

We have received your message and our team is already working on it. You can find a copy of your inquiry below:

@component('mail::panel')
**Your Message:** {{ $originalMessage }}
@endcomponent

If you have any additional information to add, please feel free to reply directly to this email.

Best regards,<br>
{{ config('app.name') }} Support Team
@endcomponent