@component('mail::message')
# Inquiry Received Successfully!

Dear {{ $name ?? 'Customer' }},

We have received your message and our team is already working on it. You can find a copy of your inquiry below:

@component('mail::panel')
**Subject:** {{ $inquiry->subject ?? 'Inquiry' }}<br>
**Your Message:** {{ $originalMessage }}
@endcomponent

If you have any additional information to add, please feel free to reply directly to this email.

Best regards,<br>
{{ config('app.name') }} Support Team
@endcomponent