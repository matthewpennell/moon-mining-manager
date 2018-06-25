@extends('layouts.master')

@section('title', 'Manual Payment')

@section('content')

    <div class="row">

        <div class="col-12">

            <p>Use this form to record a new payment that was submitted via a separate route than normal.

            <div class="card information inline-form">

                <form method="post" action="/payment/new">

                    {{ csrf_field() }}

                    <select id="miner_id" name="miner_id">
                        <option value="">Select a miner:</option>
                        @foreach ($miners as $miner)
                            <option value="{{ $miner->eve_id }}">{{ $miner->name }}</option>
                        @endforeach
                    </select>

                    or

                    <select id="rental_id" name="rental_id">
                        <option value="">Select an active renter:</option>
                        @foreach ($renters as $renter)
                            @if (!isset($renter->end_date) || strtotime($renter->end_date) >= time())
                                <option value="{{ $renter->id }}">{{ $renter->character->name }} ({{ $renter->refinery->name }})</option>
                                </tr>
                            @endif
                        @endforeach
                    </select>

                    <br><br>

                    <label for="amount">Amount:</label>
                    <input id="amount" type="text" size="15" name="amount"> ISK

                    <div class="form-actions">
                        <button type="submit">Submit</button>
                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection
