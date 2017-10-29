<div class="block">

    <div class="card-heading">Income</div>

    <div class="card highlight">
        <span class="num">{{ number_format($total_income) }}</span> ISK
    </div>

    @foreach ($refineries as $refinery)
        @include('common.card', [
            'link' => '/refinery/' . $refinery->observer_id,
            'size' => 'small',
            'avatar' => 'https://imageserver.eveonline.com/Render/35835_128.png',
            'name' => $refinery->name . ' (' . $refinery->system->solarSystemName . ')', 
            'amount' => $refinery->income
        ])
    @endforeach

</div>
