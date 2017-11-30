@extends('layouts.master')

@section('title', 'Reports')

@section('content')

    <div class="row">

        <div class="col-12">

            <div class="card-heading">Daily mining ledger</div>
            
            <div class="card">
                <canvas id="chart-mining"></canvas>
                <script>
                    window.addEventListener('load', function () {
                        var ctx = document.getElementById("chart-mining").getContext('2d');
                        var myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: [
                                    @foreach ($daily_mining as $row)
                                        '{{ date('m-d', strtotime($row->order_year . '-' . $row->order_month . '-' . str_pad($row->order_day, 2, '0', STR_PAD_LEFT))) }}',
                                    @endforeach
                                ],
                                datasets: [{
                                    data: [
                                        @foreach ($daily_mining as $row)
                                            '{{ $row->quantity }}',
                                        @endforeach
                                    ],
                                    backgroundColor: '#ffc6ce',
                                    borderColor: 'd9cacc',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: false,
                                maintainAspectRatio: true,
                                legend: {
                                    display: false
                                },
                                scales: {
                                    xAxes: [{
                                        time: {
                                            unit: 'day'
                                        }
                                    }],
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                    });
                </script>
            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-12">

            <div class="card-heading">Daily income ledger</div>
            
            <div class="card">
                <canvas id="chart-income"></canvas>
                <script>
                    window.addEventListener('load', function () {
                        var ctx = document.getElementById("chart-income").getContext('2d');
                        var myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: [
                                    @foreach ($daily_income as $row)
                                        '{{ date('m-d', strtotime($row->order_year . '-' . $row->order_month . '-' . str_pad($row->order_day, 2, '0', STR_PAD_LEFT))) }}',
                                    @endforeach
                                ],
                                datasets: [{
                                    data: [
                                        @foreach ($daily_income as $row)
                                            '{{ $row->amount }}',
                                        @endforeach
                                    ],
                                    backgroundColor: '#ffc6ce',
                                    borderColor: 'd9cacc',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: false,
                                maintainAspectRatio: true,
                                legend: {
                                    display: false
                                },
                                scales: {
                                    xAxes: [{
                                        time: {
                                            unit: 'day'
                                        }
                                    }],
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                    });
                </script>
            </div>

        </div>

    </div>

@endsection
