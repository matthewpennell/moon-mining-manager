@extends('layouts.master')

@section('title', 'Miner Details')

@section('content')

    <div class="row">

        <div class="col-4">
            @include('common.card', [
                'avatar' => $miner->avatar,
                'name' => $miner->name, 
                'sub' => $miner->corporation->name
            ])
        </div>

        <div class="col-4">
            <div class="card highlight">
                <span class="num">{{ number_format($miner->total_payments) }}</span> ISK
            </div>
        </div>

        <div class="col-4">
            <div class="card highlight negative">
                <span class="num">{{ number_format($miner->amount_owed) }}</span> ISK
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
                    @foreach ($activity_log as $date => $activity)
                        <tr>
                            <td>
                                @if (isset($activity['amount']))
                                    Invoice sent
                                @endif
                                @if (isset($activity['quantity']))
                                    Mining
                                @endif
                                @if (isset($activity['amount_received']))
                                    Payment received
                                @endif
                            </td>
                            <td class="numeric">
                                @if (isset($activity['amount']))
                                    {{ number_format($activity['amount']) }} ISK
                                @endif
                                @if (isset($activity['amount_received']))
                                    {{ number_format($activity['amount_received']) }} ISK
                                @endif
                                @if (isset($activity['quantity']))
                                    -
                                @endif
                            </td>
                            <td>{{ date('g:ia, jS F Y', strtotime($date)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>

@endsection
