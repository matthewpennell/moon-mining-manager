<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>EVE Moon Mining Manager</title>

    </head>

    <body>

        <p>
            <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
            Welcome back, {{ $user->name }}! 
            <a href="/logout">Logout</a>
        </p>
        
        @include('common.navigation')

        <h1>Moon Mining Manager</h1>

        <h2>Outstanding Debts</h2>

        <table>
            <thead>
                <tr>
                    <th>Miner</th>
                    <th>Amount Owed</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td>Total Outstanding:</td>
                    <td align="right">{{ number_format($total_amount_owed) }} ISK</td>
                </tr>
            </tfoot>
            <tbody>
                @foreach ($miners as $miner)
                    <tr>
                        <td><a href="/miner/{{ $miner->eve_id }}">{{ $miner->name }}</a></td>
                        <td align="right">{{ number_format($miner->amount_owed) }} ISK</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Income Per Refinery</h2>

        <table>
            <thead>
                <tr>
                    <th>Refinery</th>
                    <th>System</th>
                    <th>Income</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="2">Total Income:</td>
                    <td align="right">{{ number_format($total_income) }} ISK</td>
                </tr>
            </tfoot>
            <tbody>
                @foreach ($refineries as $refinery)
                    <tr>
                        <td><a href="/refinery/{{ $refinery->observer_id }}">{{ $refinery->name }}</a></td>
                        <td>{{ $refinery->system->solarSystemName }}</td>
                        <td align="right">{{ number_format($refinery->income) }} ISK</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </body>

</html>