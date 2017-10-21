<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Miners &#0183; EVE Moon Mining Manager</title>

    </head>

    <body>

        <p>
            <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
            Welcome back, {{ $user->name }}! 
            <a href="/logout">Logout</a>
        </p>
        
        @include('common.navigation')

        <h1>Miners</h1>

        <table>
            <thead>
                <tr>
                    <th>Miner</th>
                    <th>Current Amount Owed</th>
                    <th>Payments to date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($miners as $miner)
                    <tr>
                        <td><a href="/miners/{{ $miner->eve_id }}">{{ $miner->name }}</a></td>
                        <td align="right">{{ number_format($miner->amount_owed) }} ISK</td>
                        <td align="right">{{ number_format($miner->total_payments) }} ISK</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </body>

</html>