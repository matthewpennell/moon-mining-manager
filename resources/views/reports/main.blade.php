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
                                    @foreach ($dates as $row)
                                        '{{ $row }}',
                                    @endforeach
                                ],
                                datasets: [{
                                    data: [
                                        @foreach ($dates as $row)
                                            @if (isset($mining[$row]))
                                                '{{ $mining[$row] }}',
                                            @else
                                                '0',
                                            @endif
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
                                    @foreach ($dates as $row)
                                        '{{ $row }}',
                                    @endforeach
                                ],
                                datasets: [{
                                    data: [
                                        @foreach ($dates as $row)
                                            @if (isset($payments[$row]))
                                                '{{ $payments[$row] }}',
                                            @else
                                                '0',
                                            @endif
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
