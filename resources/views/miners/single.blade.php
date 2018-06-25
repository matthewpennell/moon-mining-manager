@extends('layouts.master')

@section('title', 'Miner Details')

@section('content')

    <div class="row">

        <div class="col-4">
            <div class="card-heading">Miner</div>
            @include('common.card', [
                'avatar' => $miner->avatar,
                'name' => $miner->name, 
                'sub' => (isset($miner->corporation->name) ? $miner->corporation->name : 'UNKNOWN'),
            ])
        </div>

        <div class="col-4">
            <div class="card-heading">Total tax paid to date</div>
            <div class="card highlight">
                <span class="num">{{ number_format($miner->total_payments) }}</span> ISK
            </div>
        </div>

        <div class="col-4">
            <div class="card-heading">Current amount owed</div>
            <div class="card highlight negative">
                <span class="num">{{ number_format($miner->amount_owed) }}</span> ISK
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-8">

            <div class="card-heading">Activity Log (<a href="/payment/new">Payment received</a>)</div>

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
                                @if (isset($activity->quantity))
                                    Mining {{ $activity->type->typeName }} ({{ number_format($activity->quantity, 0) }} units)
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
                                @if (isset($activity->quantity))
                                    @if (isset($activity->tax_amount))
                                        {{ number_format($activity->tax_amount) }} ISK
                                    @else
                                        -
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if (isset($activity->quantity))
                                    {{ date('jS F Y', strtotime($activity->created_at)) }}
                                @else
                                    {{ date('g:ia, jS F Y', strtotime($activity->created_at)) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>

@endsection
