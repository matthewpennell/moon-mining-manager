@extends('layouts.master')

@section('title', 'Material Value History')

@section('content')

    <div class="row">

        @foreach ($materials as $material)
            <div class="col-4">
                <div class="card-heading">{{ $material->type->typeName }}</div>
                <div class="card">
                    <canvas id="chart-{{ $material->materialTypeID }}"></canvas>
                    <script>
                        window.addEventListener('load', function () {
                            var ctx = document.getElementById("chart-{{ $material->materialTypeID }}").getContext('2d');
                            var myChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [
                                        @foreach ($history[$material->materialTypeID] as $row)
                                            '{{ date('m-d', strtotime($row->updated_at)) }}',
                                        @endforeach
                                    ],
                                    datasets: [{
                                        data: [
                                            @foreach ($history[$material->materialTypeID] as $row)
                                                '{{ $row->average_price }}',
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
            @if ($loop->iteration % 3 == 0)
                </div>
                <div class="row">
            @endif
        @endforeach

    </div>

@endsection
