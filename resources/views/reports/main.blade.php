@extends('layouts.master')

@section('title', 'Reports')

@section('content')

    <div class="row">

        <div class="col-6">

            <div class="card-heading">Daily mining ledger</div>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="numeric">Amount mined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daily_mining as $row)
                        <tr>
                            <td>{{ $row->order_year }}-{{ $row->order_month }}-{{ $row->order_day }}</td>
                            <td class="numeric">{{ number_format($row->quantity, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <div class="col-6">

            <div class="card-heading">Daily income ledger</div>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="numeric">Income</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daily_income as $row)
                        <tr>
                            <td>{{ $row->order_year }}-{{ $row->order_month }}-{{ $row->order_day }}</td>
                            <td class="numeric">{{ number_format($row->amount, 0) }} ISK</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>

@endsection
