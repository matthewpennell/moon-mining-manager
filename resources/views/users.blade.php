@extends('layouts.master')

@section('title', 'Settings')

@section('content')

    <div class="row">
        
        <div class="col-12">
            <div class="card-heading">Authorised users</div>
        </div>

        @foreach ($whitelisted_users as $user)
            <div class="col-4">
                <div class="card">
                    <img src="{{ $user->user->avatar }}" class="avatar">
                    <div class="primary">{{ $user->user->name }}</div>
                    <div class="secondary">Added by {{ $user->whitelister->name }}</div>
                    <div class="inline-form">
                        <form method="post" action="/access/blacklist/{{ $user->eve_id }}">
                            {{ csrf_field() }}
                            <button type="submit">Block user</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <div class="row">

        <div class="col-12">
            <div class="card-heading">Add new authorised user</div>
        </div>

        @foreach ($access_history as $user)
            <div class="col-4">
                <div class="card">
                    <img src="{{ $user->avatar }}" class="avatar">
                    <div class="primary">{{ $user->name }}</div>
                    <div class="inline-form">
                        <form method="post" action="/access/whitelist/{{ $user->eve_id }}">
                            {{ csrf_field() }}
                            <button type="submit">Make admin user</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

@endsection
