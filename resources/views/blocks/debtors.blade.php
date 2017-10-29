<div class="card-heading">Debts</div>

<!--<p><a href="/payment/new">Log a payment</a> received via alternative means</p>-->

<div class="card highlight">
    <span class="num">{{ number_format($total_amount_owed) }}</span> ISK
</div>

@foreach ($miners as $miner)
    @include('common.card', [
        'link' => '/miners/' . $miner->eve_id,
        'size' => 'small',
        'avatar' => $miner->avatar,
        'name' => $miner->name, 
        'amount' => -$miner->amount_owed
    ])
@endforeach
