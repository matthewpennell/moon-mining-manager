<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Active Extractions</title>

        <style>

            body {
                font-family: -apple-system, BlinkMacSystemFont, “Segoe UI”, Roboto, Helvetica, Arial, sans-serif;
                font-size: 14px;
                line-height: 20px;
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

            tr.past td {
                opacity: 0.333;
            }

        </style>

    </head>

    <body>

        <img src="https://wiki.braveineve.com/lib/tpl/vector/user/logo.png" alt="Brave Collective" class="logo">

        <h1>Active Alliance Moon Mining Extraction Timers</h1>

        <h1 id="current_time">{{ date('H:i:s') }}</h1>

        <table>
            <thead>
                <tr>
                    <th>System</th>
                    <th>Refinery name</th>
                    <th>Detonation time</th>
                    @if ($is_admin_corporation_member)
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
                                {{ date('H:i l jS F', strtotime($timer->chunk_arrival_time)) }}
                                <br>
                                <a href="http://time.nakamura-labs.com/?#{{ strtotime($timer->chunk_arrival_time) }}" target="_blank">Timezone conversion</a>
                            @else
                                {{ date('H:i l jS F', strtotime($timer->natural_decay_time)) }}
                                <br>
                                <a href="http://time.nakamura-labs.com/?#{{ strtotime($timer->natural_decay_time) }}" target="_blank">Timezone conversion</a>
                            @endif
                        </td>
                        @if ($is_admin_corporation_member)
                            <td class="admin">
                                @if ($timer->claimed_by_primary)
                                    <img src="{{ $timer->primary->avatar }}" alt="" class="avatar">
                                    {{ $timer->primary->name }}
                                    @if (strtotime($timer->natural_decay_time) >= time())
                                        <a href="/timers/clear/1/{{ $timer->observer_id }}">Remove</a>
                                    @endif
                                @else
                                    @if (strtotime($timer->natural_decay_time) >= time())
                                        <a href="/timers/claim/1/{{ $timer->observer_id }}">Claim</a>
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
                                        <a href="/timers/claim/2/{{ $timer->observer_id }}">Claim</a>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

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
