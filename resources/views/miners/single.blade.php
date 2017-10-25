@extends('layouts.master')

@section('title', 'Miner Details')

@section('content')

    <h2>Miner Details: {{ $miner->name }}</h2>

    <p>Total paid to date: {{ number_format($miner->total_payments) }} ISK</p>

    <p>Currently owes: {{ number_format($miner->amount_owed) }} ISK</p>

    <div class="block">

        <h2>Activity Log</h2>

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

@endsection
