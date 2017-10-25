@extends('layouts.master')

@section('title', 'Home')

@section('content')

    <h2>Record Payment</h2>

    <p>Use this form to record a new payment that was submitted via a separate route than normal.

    <p>
        <form method="post" action="/payment/new">
            {{ csrf_field() }}
            <label for="miner_id">Miner:</label>
            <select id="miner_id" name="miner_id">
                @foreach ($miners as $miner)
                    <option value="{{ $miner->eve_id }}">{{ $miner->name }}</option>
                @endforeach
            </select>
            <label for="amount">Amount:</label>
            <input id="amount" type="text" size="10" name="amount"> ISK
            <button type="submit">Submit</button>
        </form>
    </p>

@endsection
