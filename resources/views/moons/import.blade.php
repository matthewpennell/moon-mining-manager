@extends('layouts.master')

@section('title', 'Moon Composition Data')

@section('content')

    <div class="row">

        <div class="col-12">
            <div class="card-heading">Existing Moon Data (<a href="/moons/calculate">Calculate monthly rents</a>)</div>
            <table id="moons">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Region</th>
                        <th>System</th>
                        <th>P</th>
                        <th>M</th>
                        <th>Mineral composition</th>
                        <th class="numeric">Monthly fee</th>
                        <th class="numeric">Last month</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($moons as $moon)
                        <tr>
                            <td>{{ $moon->id }}</td>
                            <td>{{ $moon->region->regionName }}</td>
                            <td>{{ $moon->system->solarSystemName }}</td>
                            <td>{{ $moon->planet }}</td>
                            <td>{{ $moon->moon }}</td>
                            <td>
                                {{ $moon->mineral_1->typeName }} ({{ $moon->mineral_1_percent }}%)
                                &#0183; {{ $moon->mineral_2->typeName }} ({{ $moon->mineral_2_percent }}%)
                                @if ($moon->mineral_3_type_id)
                                &#0183; {{ $moon->mineral_3->typeName }} ({{ $moon->mineral_3_percent }}%)
                                    @if ($moon->mineral_4_type_id)
                                        &#0183; {{ $moon->mineral_4->typeName }} ({{ $moon->mineral_4_percent }}%)
                                    @endif
                                @endif
                            </td>
                            <td class="numeric">{{ number_format($moon->monthly_rental_fee, 0) }}</td>
                            <td class="numeric">{{ number_format($moon->previous_monthly_rental_fee, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <div class="row">

        <div class="col-12">
            <div class="card-heading">Import Moon Data</div>
            <div class="card">
                <form action="/moons/import" method="post">
                    {{ csrf_field() }}
                    <textarea name="data" rows="50" cols="200" placeholder="Paste raw moon data here from spreadsheet in the following format (columns): Region name, System name, Planet number, Moon number, Renter name, Mineral 1 name, Mineral 1 %, Mineral 2 name, Mineral 2 %, [Mineral 3 name, Mineral 3 %, [Mineral 4 name, Mineral 4 %]]"></textarea>
                    <div class="form-actions">
                        <button type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
    
        window.addEventListener('load', function () {
            $('#moons').tablesorter();
        });
    
    </script>

@endsection
