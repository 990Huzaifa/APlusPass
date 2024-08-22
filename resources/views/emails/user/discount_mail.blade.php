<x-mail::message>
    <div style="margin: 0px auto; width: 100%; text-align: center;">
        <img src="{{ $message->embed(public_path('img/new-logo.png')) }}" alt="Logo" width="300px">
    </div>
    <h1>Congratulations, {{ $data['name'] }}!</h1>
    <p>We're excited to have you with us at {{ env('APP_NAME') }}.</p>
    <p>As a token of our appreciation, we're pleased to offer you an exclusive discount on your next purchase.</p>
    <h2>Your Discount Code: <strong>{{ $data['code'] }}</strong></h2>
    <p>Use this code at checkout to enjoy a {{ $data['discount'] }}% discount on your selected courses.</p>
    <p><strong>Note:</strong> This code is valid until {{ $data['expiry_date'] }}. Don't miss out on this great offer!</p>
    <p>If you have any questions or need assistance, feel free to contact our support team.</p>
    <p>Happy learning!</p>
    <p>Thanks,</p>
    <p>The {{ env('APP_NAME') }} Team</p>
</x-mail::message>
