<x-mail::message>
    <div style="text-align: center; margin: 0 auto 20px auto;">
        <img src="{{ $message->embed(public_path('img/new-logo.png')) }}" alt="Logo" width="300px">
    </div>
    <div style="text-align: center; font-size: 28px; font-weight: bold; color: #ff6600; margin-bottom: 20px;">
        Welcome to {{ env('APP_NAME') }}
    </div>
    <div style="font-size: 16px; color: #555;">
        <p style="margin-bottom: 15px;">Hello Admin,</p>
        <p style="margin-bottom: 15px;">We have received a notification that a user has calculated the total amount for their selected course(s) and has proceeded to the checkout:</p>
        <p style="margin-bottom: 15px;"><span style="color: #ff6600; font-weight: bold;">Name:</span> {{ $data['fullname'] }}</p>
        <p style="margin-bottom: 15px;"><span style="color: #ff6600; font-weight: bold;">Email:</span> {{ $data['email'] }}</p>
        <p style="margin-bottom: 15px;"><span style="color: #ff6600; font-weight: bold;">Phone:</span> {{ $data['phone'] }}</p>
        <p style="margin-bottom: 15px;"><span style="color: #ff6600; font-weight: bold;">Courses:</span> {{ $data['courses'] }}</p>
        <p style="margin-bottom: 15px;"><span style="color: #ff6600; font-weight: bold;">Amount:</span> ${{ $data['amount'] }}</p>
        <p style="margin-bottom: 15px;">Thank you for your attention.</p>
    </div>
    <div style="text-align: center; margin-top: 30px; font-size: 14px; color: #999;">
        Best Regards,<br>
        {{ env('APP_NAME') }}
    </div>
</x-mail::message>