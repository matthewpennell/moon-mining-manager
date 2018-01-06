@extends('layouts.master')

@section('title', 'Refinery Details')

@section('content')

    <div class="row">

        <div class="col-4">
            <div class="card-heading">Refinery</div>
            @include('common.card', [
                'avatar' => 'https://imageserver.eveonline.com/Render/35835_128.png',
                'name' => $renter->refinery->name, 
                'sub' => $renter->refinery->system->solarSystemName
            ])
        </div>

        <div class="col-4">
            <div class="card-heading">Rented by</div>
            @include('common.card', [
                'link' => '/renters/character/' . $renter->character_id,
                'avatar' => $renter->character->avatar->px128x128,
                'name' => $renter->character->name, 
                'sub' => $renter->character->corporation->name
            ])
        </div>

        <div class="col-4">
            <div class="card-heading">Monthly rent</div>
            <div class="card highlight">
                <span class="num">{{ number_format($renter->monthly_rental_fee) }}</span> ISK
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-8">

            <div class="card-heading">Activity Log</div>

            <table>
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th class="numeric">Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activity_log as $activity)
                        <tr>
                            <td>
                                @if (isset($activity->amount))
                                    Invoice sent
                                @endif
                                @if (isset($activity->amount_received))
                                    Payment received
                                @endif
                            </td>
                            <td class="numeric">
                                @if (isset($activity->amount))
                                    {{ number_format($activity->amount) }} ISK
                                @endif
                                @if (isset($activity->amount_received))
                                    {{ number_format($activity->amount_received) }} ISK
                                @endif
                            </td>
                            <td>
                                {{ date('g:ia, jS F Y', strtotime($activity->created_at)) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>

@endsection
