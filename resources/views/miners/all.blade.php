@extends('layouts.master')

@section('title', 'Miners')

@section('content')

    <h2>Miners</h2>

    <div class="block">
        <table>
            <thead>
                <tr>
                    <th>Miner</th>
                    <th>Current Amount Owed</th>
                    <th>Payments to date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($miners as $miner)
                    <tr>
                        <td><a href="/miners/{{ $miner->eve_id }}">{{ $miner->name }}</a></td>
                        <td align="right">{{ number_format($miner->amount_owed) }} ISK</td>
                        <td align="right">{{ number_format($miner->total_payments) }} ISK</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
