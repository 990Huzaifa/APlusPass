<x-mail::message>
        <div style="margin: 0px auto; width: 100%; text-align: center;">
            <img src="{{ $message->embed(public_path('img/new-logo.png')) }}" alt="Logo" width="300px">
        </div>
        <h1 style="text-align: center; color: #333;">Welcome to {{ env('APP_NAME') }}</h1>
        <p>Hi {{$data['payerName']}},</p>
        <p>Your payment succeed {{$data['description']}}</p>
        <p>Total: <b>${{$data['amount']}}</b></p>
        <p>Thank you for your purchase!</p>
        <p style="text-align: center;">{{ env('APP_NAME') }}</p>
</x-mail::message>