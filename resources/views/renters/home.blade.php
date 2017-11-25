@extends('layouts.master')

@section('title', 'Renters')

@section('content')

    <div class="row">

        <div class="col-8">

            <div class="card-heading">All current renters <a href="/renters/new">[Add new]</a></div>
            
            <table>
                <thead>
                    <tr>
                        <th>Moon</th>
                        <th>Rental contact</th>
                        <th>Details</th>
                        <th class="numeric">Start date</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($renters as $renter)
                        <tr>
                            <td>
                                @if (isset($renter->refinery_id))
                                    {{ $renter->refinery->name }} ({{ $renter->refinery->system->solarSystemName }})
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $renter->character->name }}</td>
                            <td>{{ $renter->notes }}</td>
                            <td class="numeric">{{ date('jS F Y', strtotime($renter->start_date)) }}</td>
                            <td><a href="/renters/{{ $renter->id }}">Edit details</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>

@endsection
