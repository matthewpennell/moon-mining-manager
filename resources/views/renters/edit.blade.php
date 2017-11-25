@extends('layouts.master')

@section('title', 'Edit Renter')

@section('content')

    @if ($errors->any())
        <div class="row">
            <div class="col-6">
                <div class="card-heading">Errors!</div>
                <div class="card errors">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <form method="post" action="/renters/{{ $renter->id }}">

        {{ csrf_field() }}

        <div class="row">

            <div class="col-6">

                <div class="card-heading">Renter details</div>

                <div class="card information">
                    <div>
                        <label for="type">Type of rental</label>
                        <select id="type" name="type">
                            <option value="individual"{{ ($renter->type == 'individual') ? ' selected' : '' }}>Individual</option>
                            <option value="corporation"{{ ($renter->type == 'corporation') ? ' selected' : '' }}>Corporation</option>
                        </select>
                    </div>
                    <div>
                        <label for="character_id">Character</label>
                        <input type="text" id="character_id" name="character_id" value="{{ $renter->character_id }}">
                    </div>
                    <div>
                        <label for="refinery_id">Refinery to be rented</label>
                        <select id="refinery_id" name="refinery_id">
                            <option value="">Select refinery:</option>
                            @foreach ($refineries as $refinery)
                                <option value="{{ $refinery->observer_id }}"{{ ($renter->refinery_id == $refinery->observer_id) ? ' selected' : '' }}>{{ $refinery->name }}</option>
                            @endforeach
                        </select>
                        <span>(if not yet dropped, enter details below)</span>
                    </div>
                    <div>
                        <label for="monthly_rental_fee">Monthly rental fee</label>
                        <input type="text" id="monthly_rental_fee" name="monthly_rental_fee" value="{{ $renter->monthly_rental_fee }}">
                    </div>
                    <div>
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ $renter->notes }}</textarea>
                    </div>
                    <div>
                        <label for="start_date">Contract start date</label>
                        <input type="text" id="start_date" name="start_date" value="{{ $renter->start_date }}">
                    </div>
                    <div>
                        <label for="end_date">Contract end date</label>
                        <input type="text" id="end_date" name="end_date" value="{{ $renter->end_date }}" placeholder="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-actions">
                        <button type="submit">Save changes</button>
                    </div>
                </div>

            </div>

        </div>

    </form>

@endsection
