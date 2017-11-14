@if (isset($link))
    <a class="card card-{{ (isset($size)) ? $size : 'regular' }}{{ (isset($is_active)) ? ' refinery-active' : '' }}"{!! (isset($is_active)) ? ' title="Mining cycle due to complete at ' . date('H:i, l jS F', strtotime($is_active)) . '"' : '' !!} href="{{ $link }}">
@else
    <div class="card card-{{ (isset($size)) ? $size : 'regular' }}{{ (isset($is_active)) ? ' refinery-active' : '' }}"{!! (isset($is_active)) ? ' title="Mining cycle due to complete on ' . date('H:i, l jS F', strtotime($is_active)) . '"' : '' !!}>
@endif
<img src="{{ $avatar }}" class="avatar" alt="">
<div class="primary">{!! $name !!}</div>
@if (isset($sub))
    <div class="secondary">{{ $sub }}</div>
@endif
@if (isset($amount))
    <div class="amount {{ ($amount >= 0) ? 'num-positive' : 'num-negative' }}">
        @if ($amount >= 0)
            {{ number_format($amount) }} ISK
        @else
            {{ number_format($amount - $amount - $amount) }} ISK
        @endif
    </div>
@endif
@if (isset($link))
    </a>
@else
    </div>
@endif
