@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

    <div class="row">

        <div class="col-4">
            <div class="card-heading">Top miner</div>
            @include('common.card', [
                'avatar' => $top_miner->avatar,
                'name' => $top_miner->name, 
                'sub' => $top_miner->corporation->name, 
                'amount' => $top_miner->total
            ])
        </div>

        <div class="col-4">
            <div class="card-heading">Top Refinery</div>
            @include('common.card', [
                'avatar' => 'https://imageserver.eveonline.com/Render/35835_128.png',
                'name' => $top_refinery->name, 
                'sub' => $top_refinery->system->solarSystemName, 
                'amount' => $top_refinery->income
            ])
        </div>

        <div class="col-4">
            <div class="card-heading">Top System</div>
            @include('common.card', [
                'avatar' => 'https://imageserver.eveonline.com/Type/3802_64.png',
                'name' => $top_system->solarSystemName, 
                'sub' => 'Catch', 
                'amount' => $top_system->total
            ])
        </div>

    </div>

    <div class="row">

        <div class="col-6">
            @include('blocks.debtors')
        </div>

        <div class="col-6">
            @include('blocks.refineries')
            @include('blocks.ninjas')
        </div>
    
    </div>

@endsection
