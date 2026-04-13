<x-mail::message>
# Welcome to MasterPOS, {{ $ownerName }}!

Your business **{{ $businessName }}** has been onboarded successfully.

### Login details
- Email: `{{ $email }}`
- Password: Use the password you set during registration.

<x-mail::button :url="$loginUrl">
Open MasterPOS Login
</x-mail::button>

API documentation: {{ $apiDocsUrl }}

If your trial expires, upgrade your plan from billing to continue uninterrupted service.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
