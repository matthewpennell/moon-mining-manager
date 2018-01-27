@extends('layouts.master')

@section('title', 'Renters')

@section('content')

    <div class="row">

        <div class="col-12">

            <div class="card-heading">All current renters <a href="/renters/new">[Add new]</a></div>
            
            <table>
                <thead>
                    <tr>
                        <th>Moon</th>
                        <th>Rental contact</th>
                        <th>Notes</th>
                        <th class="numeric">Monthly fee</th>
                        <th class="numeric">Currently owed</th>
                        <th class="numeric">Start date</th>
                        <th class="numeric">End date</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($renters as $renter)
                        <tr>
                            <td>
                                @if (isset($renter->refinery_id))
                                    <a href="/renters/refinery/{{ $renter->refinery_id }}">{{ $renter->refinery->name }} ({{ $renter->refinery->system->solarSystemName }})</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td><a href="/renters/character/{{ $renter->character_id }}">{{ $renter->character->name }}</a></td>
                            <td>{{ $renter->notes }}</td>
                            <td class="numeric">{{ number_format($renter->monthly_rental_fee, 0) }} ISK</td>
                            <td class="numeric">{{ number_format($renter->amount_owed, 0) }} ISK</td>
                            <td class="numeric">{{ date('jS F Y', strtotime($renter->start_date)) }}</td>
                            <td class="numeric">
                                @if (isset($renter->end_date))
                                    {{ date('jS F Y', strtotime($renter->end_date)) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td><a href="/renters/{{ $renter->id }}">Edit details</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>

@endsection
