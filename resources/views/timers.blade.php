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

            th, td {
                text-align: left;
                padding: 0 20px 10px 0;
            }

        </style>

    </head>

    <body>

        <h1>Active Alliance Moon Mining Extraction Timers</h1>

        <table>
            <thead>
                <tr>
                    <th>System</th>
                    <th>Refinery name</th>
                    <th>Chunk arrival time</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($timers as $timer)
                    <tr>
                        <td>
                            {{ $timer->system->solarSystemName }} 
                            ({{ $timer->system->region->regionName }}) 
                            <a href="http://evemaps.dotlan.net/map/{{ str_replace(' ', '_', $timer->system->region->regionName) }}/{{ $timer->system->solarSystemName }}">View on Dotlan</a>
                        </td>
                        <td>{{ $timer->name }}</td>
                        <td>{{ date('H:i l jS F', strtotime($timer->chunk_arrival_time)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

</body>

</html>
