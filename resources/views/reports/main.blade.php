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
                                            beginAtZero: true,
                                            callback: function (value) {
                                                return value.toFixed(0).replace(/(\d)(?=(\d{3})+$)/g, '$1,');;
                                            }
                                        }
                                    }]
                                },
                                tooltips: {
                                    callbacks: {
                                        label: function(tooltipItem, data) {
                                            return tooltipItem.yLabel.toFixed(0).replace(/(\d)(?=(\d{3})+$)/g, '$1,') + ' m³';
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
                <div class="report-navigation">
                    <a href="/reports/{{ ($month == 1) ? $year - 1 : $year }}/{{ str_pad($prev_month, 2, "0", STR_PAD_LEFT) }}">&laquo; Previous month</a> | <a href="/reports/{{ ($month == 12) ? $year + 1 : $year }}/{{ str_pad($next_month, 2, "0", STR_PAD_LEFT) }}">Next month &raquo;</a>
                </div>
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
                                            beginAtZero: true,
                                            callback: function (value) {
                                                return value.toFixed(0).replace(/(\d)(?=(\d{3})+$)/g, '$1,');
                                            }
                                        }
                                    }]
                                },
                                tooltips: {
                                    callbacks: {
                                        label: function(tooltipItem, data) {
                                            return tooltipItem.yLabel.toFixed(0).replace(/(\d)(?=(\d{3})+$)/g, '$1,') + ' ISK';
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
                <div class="report-navigation">
                    <a href="/reports/{{ ($month == 1) ? $year - 1 : $year }}/{{ str_pad($prev_month, 2, "0", STR_PAD_LEFT) }}">&laquo; Previous month</a> | <a href="/reports/{{ ($month == 12) ? $year + 1 : $year }}/{{ str_pad($next_month, 2, "0", STR_PAD_LEFT) }}">Next month &raquo;</a>
                </div>
            </div>

        </div>

    </div>

@endsection
