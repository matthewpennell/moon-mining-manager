<div class="card-heading">Current outstanding debts (<a href="/payment/new">Manual payment</a>)</div>

<div class="card highlight">
    <span class="num">{{ number_format($total_amount_owed) }}</span> ISK
</div>

@foreach ($miners as $miner)
    @include('common.card', [
        'link' => '/miners/' . $miner->eve_id,
        'size' => 'small',
        'avatar' => $miner->avatar,
        'name' => ($miner->latest_payment) ? $miner->name . ' <span class="latest-payment">' . date('M j', strtotime($miner->latest_payment)) . '</span>' : $miner->name,
        'amount' => -$miner->amount_owed
    ])
@endforeach
