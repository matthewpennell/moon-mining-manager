@extends('layouts.master')

@section('title', 'Miners')

@section('content')

    <div class="row">

    @foreach ($miners as $miner)
        <div class="col-4">
            @include('common.card', [
                'link' => '/miners/' . $miner->eve_id,
                'avatar' => $miner->avatar,
                'name' => $miner->name,
                'sub' => $miner->corporation->name,
                'amount' => $miner->total_payments
            ])
        </div>
        @if (($loop->index + 1) % 3 == 0)
            </div>
            <div class="row">
        @endif
    @endforeach

    </div>

@endsection
