<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $miner->name }} &#0183; EVE Moon Mining Manager</title>

    </head>

    <body>

        <p>
            <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
            Welcome back, {{ $user->name }}! 
            <a href="/logout">Logout</a>
        </p>
        
        @include('common.navigation')

        <h1>Miner Details: {{ $miner->name }}</h1>

        <h2>Total paid to date: {{ number_format($miner->total_payments) }} ISK</h2>

        <h3>Currently owes: {{ number_format($miner->amount_owed) }} ISK</h3>

        <h2>Activity Log</h2>

        <table>
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activity_log as $date => $activity)
                    <tr>
                        <td>
                            @if (isset($activity['amount']))
                                Invoice sent
                            @endif
                            @if (isset($activity['quantity']))
                                Mining
                            @endif
                            @if (isset($activity['amount_received']))
                                Payment received
                            @endif
                        </td>
                        <td align="right">
                            @if (isset($activity['amount']))
                                {{ number_format($activity['amount']) }} ISK
                            @endif
                            @if (isset($activity['amount_received']))
                                {{ number_format($activity['amount_received']) }} ISK
                            @endif
                            @if (isset($activity['quantity']))
                                -
                            @endif
                        </td>
                        <td>{{ date('g:ia, jS F Y', strtotime($date)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </body>

</html>