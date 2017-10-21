<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Payment Details &#0183; EVE Moon Mining Manager</title>

    </head>

    <body>

        <p>
            <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
            Welcome back, {{ $user->name }}! 
            <a href="/logout">Logout</a>
        </p>
        
        @include('common.navigation')
        
        <h1>Record Payment</h1>

        <p>Use this form to record a new payment that was submitted via a separate route than normal.

        <p>
            <form method="post" action="/payment/new">
                {{ csrf_field() }}
                <label for="miner_id">Miner:</label>
                <select id="miner_id" name="miner_id">
                    @foreach ($miners as $miner)
                        <option value="{{ $miner->eve_id }}">{{ $miner->name }}</option>
                    @endforeach
                </select>
                <label for="amount">Amount:</label>
                <input id="amount" type="text" size="10" name="amount"> ISK
                <button type="submit">Submit</button>
            </form>
        </p>

    </body>

</html>