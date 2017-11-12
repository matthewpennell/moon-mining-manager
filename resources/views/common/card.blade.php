@if (isset($link))
    <a class="card card-{{ (isset($size)) ? $size : 'regular' }}" href="{{ $link }}">
@else
    <div class="card card-{{ (isset($size)) ? $size : 'regular' }}">
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
