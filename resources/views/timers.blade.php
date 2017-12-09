<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Active Extractions</title>

        <style>

            * {
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, “Segoe UI”, Roboto, Helvetica, Arial, sans-serif;
                font-size: 14px;
                line-height: 20px;
            }

            body.bar {
                padding-top: 100px;
            }

            .logo {
                display: block;
                margin: 10px auto;
            }

            h1 {
                text-align: center;
                margin: 20px 0 40px;
            }

            h2 {
                font-size: 20px;
                font-weight: bold;
                margin: 0;
            }

            h3 {
                font-size: 16px;
                font-weight: normal;
                margin: 0;
            }

            table {
                width: 80%;
                margin: 20px auto;
                border-collapse: collapse;
            }

            th, td {
                text-align: left;
                padding: 10px;
                border: 5px solid #eee;
            }

            th {
                background: #eee;
            }

            td form label, td form input {
                display: block;
                margin: 0 auto;
                text-align: center;
            }

            .avatar {
                width: 50px;
                height: 50px;
                border-radius: 50px;
            }

            .admin {
                text-align: center;
            }

            .admin img {
                display: block;
                margin: 0 auto 10px;
            }

            .admin a {
                display: block;
            }

            tr.past td {
                opacity: 0.333;
            }

            .miner-bar {
                background: #242626;
                color: #fff;
                position: fixed;
                top: 0;
                left: 0;
                height: 100px;
                width: 100%;
            }

            .miner-identity,
            .miner-amount-owed,
            .miner-total-income,
            .miner-activity-log {
                float: left;
                width: 25%;
            }

            .miner-identity {
                font-weight: bold;
                font-size: 20px;
                overflow: hidden;
                padding: 30px 0 0 110px;
            }

            .miner-identity img {
                width: 100px;
                height: 100px;
                position: absolute;
                top: 0;
                left: 0;
            }

            .miner-bar a {
                display: block;
                color: #fff;
                font-size: 12px;
                font-weight: normal;
            }

            .miner-bar a:hover {
                text-decoration: none;
                color: #cad9d7;
            }

            .miner-bar .heading {
                text-transform: uppercase;
                font-size: 12px;
                display: block;
                margin: 20px 0 10px;
            }

            .miner-bar .numeric {
                font-size: 30px;
            }

            .mining-activity {
                width: 500px;
                margin: 20px auto;
            }

            .mining-activity ul {
                list-style: none;
                padding: 0;
            }

        </style>

    </head>

    <body
        @if ($miner)
            class="bar"
        @endif
    >

        @if ($miner)
            <div class="miner-bar">
                <div class="miner-identity">
                    <img src="{{ $miner->avatar }}" alt="">
                    {{ $miner->name }}
                    <a href="/logout">Logout</a>
                </div>
                <div class="miner-amount-owed">
                    <span class="heading">Current amount owed:</span>
                    <span class="numeric">{{ number_format($miner->amount_owed, 0) }}</span> ISK
                </div>
                <div class="miner-total-income">
                    <span class="heading">Total payments to date:</span>
                    <span class="numeric">{{ number_format($miner->total_payments, 0) }}</span> ISK
                </div>
                @if ($activity_log)
                    <div class="miner-activity-log">
                        <span class="heading">Activity record:</span>
                        <a href="#activity-log" id="show-log">View my mining activity log</a>
                    </div>
                @endif
            </div>
        @endif

        <img src="https://wiki.braveineve.com/lib/tpl/vector/user/logo.png" alt="Brave Collective" class="logo">

        <h1>Active Alliance Moon Mining Extraction Timers</h1>

        <h1 id="current_time">{{ date('H:i:s') }}</h1>

        <table>
            <thead>
                <tr>
                    <th>System</th>
                    <th>Refinery name</th>
                    <th>Detonation time</th>
                    @if ($is_whitelisted_user)
                        <th class="admin">Primary</th>
                        <th class="admin">Secondary</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($timers as $timer)
                    <tr
                        @if (strtotime($timer->natural_decay_time) < time())
                            class="past"
                        @endif
                    >
                        <td>
                            <h2>{{ $timer->system->solarSystemName }}</h2>
                            <h3>{{ $timer->system->region->regionName }}</h3>
                            <a href="http://evemaps.dotlan.net/map/{{ str_replace(' ', '_', $timer->system->region->regionName) }}/{{ $timer->system->solarSystemName }}">View on Dotlan</a>
                        </td>
                        <td>{{ $timer->name }}</td>
                        <td>
                            @if ($timer->claimed_by_primary || $timer->claimed_by_secondary)
                                {{ date('H:i l jS F', strtotime($timer->detonation_time)) }}
                                <br>
                                <a href="http://time.nakamura-labs.com/?#{{ strtotime($timer->chunk_arrival_time) }}" target="_blank">Timezone conversion</a>
                            @else
                                {{ date('H:i l jS F', strtotime($timer->natural_decay_time)) }}
                                <br>
                                <a href="http://time.nakamura-labs.com/?#{{ strtotime($timer->natural_decay_time) }}" target="_blank">Timezone conversion</a>
                            @endif
                        </td>
                        @if ($is_whitelisted_user)
                            <td class="admin">
                                @if ($timer->claimed_by_primary)
                                    <img src="{{ $timer->primary->avatar }}" alt="" class="avatar">
                                    {{ $timer->primary->name }}
                                    @if (strtotime($timer->natural_decay_time) >= time())
                                        <a href="/timers/clear/1/{{ $timer->observer_id }}">Remove</a>
                                    @endif
                                @else
                                    @if (strtotime($timer->natural_decay_time) >= time())
                                        <form method="post" action="/timers/claim/1/{{ $timer->observer_id }}">
                                            {{ csrf_field() }}
                                            <label for="detonation">Enter detonation time ({{ date('H:i', strtotime($timer->chunk_arrival_time)) }}-{{ date('H:i', strtotime($timer->natural_decay_time)) }})</label>
                                            <input id="detonation" name="detonation" type="text" size="10">
                                            <button type="submit">Claim detonation</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                            <td class="admin">
                                @if ($timer->claimed_by_secondary)
                                    <img src="{{ $timer->secondary->avatar }}" alt="" class="avatar">
                                    {{ $timer->secondary->name }}
                                    @if (strtotime($timer->natural_decay_time) >= time())
                                        <a href="/timers/clear/2/{{ $timer->observer_id }}">Remove</a>
                                    @endif
                                @else
                                    @if (strtotime($timer->natural_decay_time) >= time())
                                        <form method="post" action="/timers/claim/2/{{ $timer->observer_id }}">
                                            {{ csrf_field() }}
                                            <label for="detonation">Enter detonation time  ({{ date('H:i', strtotime($timer->chunk_arrival_time)) }}-{{ date('H:i', strtotime($timer->natural_decay_time)) }})</label>
                                            <input id="detonation" name="detonation" type="text" size="10">
                                            <button type="submit">Claim detonation</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($activity_log)
            <div class="mining-activity">
                <h2>Your mining activity log</h2>
                <ul id="activity-log">
                    @foreach ($activity_log as $date => $event)
                        <li>
                            {{ date('Y-m-d', strtotime($date)) }} - 
                            @if (isset($event['amount']))
                                Invoice sent for {{ number_format($event['amount']) }} ISK
                            @endif
                            @if (isset($event['quantity']))
                                @php
                                    $refinery = App\Refinery::where('observer_id', $event['refinery_id'])->first();
                                @endphp
                                Mining recorded in {{ $refinery->system->solarSystemName }} ({{ number_format($event['quantity'], 0) }} units)
                            @endif
                            @if (isset($event['amount_received']))
                                Payment received for {{ number_format($event['amount_received']) }} ISK
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <script>

            window.onload = function () {

                setInterval(function () {
                    var x = new Date();
                    var hour = x.getUTCHours(),
                        minute = x.getUTCMinutes(),
                        second = x.getUTCSeconds();
                    document.getElementById('current_time').innerHTML = pad(hour) + ':' + pad(minute) + ':' + pad(second) + ' EVE';
                }, 1000);

            }

            function pad(num) {
                if (num == 0) return '00';
                if (num > 9) return num;
                return '0' + num;
            }

        </script>

</body>

</html>
