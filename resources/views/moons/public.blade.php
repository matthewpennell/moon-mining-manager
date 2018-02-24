<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Moons</title>

        <style>

            * {
                box-sizing: border-box;
            }

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

            p {
                text-align: center;
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

            .rented td {
                text-decoration: line-through;
                opacity: 0.333;
            }

        </style>

    </head>

    <body>

        <img src="https://wiki.braveineve.com/lib/tpl/vector/user/logo.png" alt="Brave Collective" class="logo">

        <h1>Alliance Moons</h1>

        <p>Click on table headings to sort.</p>

        <div class="row">

            <table id="moons">
                <thead>
                    <tr>
                        <th>Region</th>
                        <th>System</th>
                        <th>Mineral #1</th>
                        <th>Mineral #2</th>
                        <th>Mineral #3</th>
                        <th>Mineral #4</th>
                        <th class="numeric">Rent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($moons as $moon)
                        <tr
                            @if (isset($moon->renter))
                                class="rented"
                            @endif
                        >
                            <td>{{ $moon->region->regionName }}</td>
                            <td>{{ $moon->system->solarSystemName }}</td>
                            <td>{{ $moon->mineral_1->typeName }} ({{ $moon->mineral_1_percent }}%)</td>
                            <td>{{ $moon->mineral_2->typeName }} ({{ $moon->mineral_2_percent }}%)</td>
                            <td>
                                @if ($moon->mineral_3_type_id)
                                    {{ $moon->mineral_3->typeName }} ({{ $moon->mineral_3_percent }}%)
                                @endif
                            </td>
                            <td>
                                @if ($moon->mineral_4_type_id)
                                    {{ $moon->mineral_4->typeName }} ({{ $moon->mineral_4_percent }}%)
                                @endif
                            </td>
                            <td class="numeric">{{ number_format($moon->monthly_rental_fee, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <script src="/js/app.js"></script>

        <script>
        
            window.addEventListener('load', function () {
                $('#moons').tablesorter();
            });
        
        </script>

    </body>

</html>
