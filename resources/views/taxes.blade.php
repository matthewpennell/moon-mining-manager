<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Tax Rates &#0183; EVE Moon Mining Manager</title>

    </head>

    <body>

        <p>
            <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
            Welcome back, {{ $user->name }}! 
            <a href="/logout">Logout</a>
        </p>
        
        @include('common.navigation')
        
        <h1>Tax Rates</h1>

        <p>
            Update the tax rate for all ores to:
            <form method="post" action="/taxes/update_master_rate">
                {{ csrf_field() }}
                <input type="text" size="3" name="new_tax_rate">
                <button type="submit">Update</button>
            </form>
        </p>

        <table>
            <thead>
                <tr>
                    <th>Moon Ore</th>
                    <th>Value</th>
                    <th>Tax Rate</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tax_rates as $rate)
                    <tr>
                        <td>{{ $rate->type->typeName }}</td>
                        <td>
                            {{ number_format($rate->value, 2) }} ISK
                            <!--
                                <form method="post" action="/taxes/update_value/{{ $rate->type_id }}">
                                    {{ csrf_field() }}
                                    <input type="text" size="10" name="new_value" value="{{ round($rate->value) }}"> ISK
                                    <button type="submit">Save</button>
                                </form>
                            -->
                        </td>
                        <td>{{ round($rate->tax_rate) }}%</td>
                        <td>
                            <form method="post" action="/taxes/update_rate/{{ $rate->type_id }}">
                                {{ csrf_field() }}
                                <input type="text" size="3" name="new_tax_rate">
                                <button type="submit">Update individual tax rate</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
    </body>

</html>