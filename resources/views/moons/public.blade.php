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
                margin: 0;
                padding: 0;
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
                width: 90%;
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

            .nobreak {
                white-space: nowrap;
            }

            /* Menu */

            .public-menu {
                background: #242626;
                color: #fff;
                height: 50px;
            }

            .public-menu li {
                list-style: none;
            }

            .public-menu a {
                float: right;
                font-weight: bold;
                padding: 0 20px;
                color: #fff;
                line-height: 50px;
                text-decoration: none;
            }

            .public-menu a:hover {
                text-decoration: none;
                background: #cad9d7;
                color: #242626;
            }

        </style>

    </head>

    <body>

        @include('common.public-nav', ['page' => 'timers'])

        <img src="https://wiki.braveineve.com/lib/tpl/vector/user/logo.png" alt="Brave Collective" class="logo">

        <h1>Alliance Moons</h1>

        <p>Click on table headings to sort. To inquire about renting a moon, please evemail <a href="https://zkillboard.com/character/93533671/">Metric Candy</a> quoting the relevant moon ID.</p>

        <p>For more information on the Brave moon rental program, please consult <a href="https://wiki.braveineve.com/member/alliance/industry/moon-rental" target="_blank">this wiki page</a>.</p>

        <div class="row">

            <table id="moons">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            @if (isset($moon->renter) || $moon->alliance_owned == 1)
                                class="rented"
                            @endif
                        >
                            <td>{{ $moon->id }}</td>
                            <td>{{ $moon->region->regionName }}</td>
                            <td class="nobreak">{{ $moon->system->solarSystemName }}</td>
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
