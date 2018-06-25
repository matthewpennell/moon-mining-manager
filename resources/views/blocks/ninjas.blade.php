<div class="block">
    
    <div class="card-heading">Ninja Miners</div>

    @foreach ($ninjas as $ninja)
        @include('common.card', [
            'size' => 'small',
            'avatar' => $ninja->avatar,
            'name' => $ninja->name . ' (' . (isset($ninja->corporation->name) ? $ninja->corporation->name : 'UNKNOWN') . ')', 
            'amount' => $ninja->amount_owed
        ])
    @endforeach

</div>
