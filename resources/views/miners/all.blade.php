@extends('layouts.master')

@section('title', 'Miners')

@section('content')

    <div class="row">

        <div class="col-12">
            <table>
                <thead>
                    <th>Miner</th>
                    <th>Corporation</th>
                    <th class="numeric">Amount owed</th>
                    <th class="numeric">Total payments</th>
                    <th>Last payment</th>
                </thead>
                <tbody>
                    @foreach ($miners as $miner)
                        <tr>
                            <td>{{ $miner->name }}</td>
                            <td>{{ $miner->corporation->name }}</td>
                            <td class="numeric">{{ number_format($miner->amount_owed, 0) }} ISK</td>
                            <td class="numeric">{{ number_format($miner->total_payments, 0) }} ISK</td>
                            <td>{{ $miner->latest_payment }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

@endsection
