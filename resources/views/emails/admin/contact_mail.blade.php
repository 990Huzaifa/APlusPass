<x-mail::message>
    <div style="margin: 0px auto; width: 100%; text-align: center;">
        <img src="{{ $message->embed(public_path('img/new-logo.png')) }}" alt="Logo" width="300px">
    </div>
    <h1>Welcome to {{ env('APP_NAME') }}</h1>
    <p>Hi Admin,</p>
    <p>We have received a new user registration on your platform. Here are the details:</p>
    <p>Name: {{ $data['fullname'] }}</p>
    <p>Email: {{ $data['email'] }}</p>
    <p>Phone: {{ $data['phone'] }}</p>
    <p>Thanks,</p>
    <p>{{ env('APP_NAME') }}</p>
</x-mail::message>
