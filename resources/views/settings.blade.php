@extends('layouts.master')

@section('title', 'Settings')

@section('content')

    <div class="row">
        
        <div class="col-12">
            <div class="card-heading">Administrators</div>
        </div>

        @foreach ($admin_users as $user)
            <div class="col-4">
                <div class="card">
                    <img src="{{ $user->user->avatar }}" class="avatar">
                    <div class="primary">{{ $user->user->name }}</div>
                    <div class="secondary">Added by {{ $user->whitelister->name }}</div>
                    <div class="inline-form">
                        <form method="post" action="/access/blacklist/{{ $user->eve_id }}">
                            {{ csrf_field() }}
                            <button type="submit">Revoke admin access</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <div class="row">
        
        <div class="col-12">
            <div class="card-heading">Whitelisted detonation managers</div>
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
                            <button type="submit">Revoke whitelisted status</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <div class="row">

        <div class="col-12">
            <div class="card-heading">Other users</div>
        </div>

        @foreach ($access_history as $user)
            <div class="col-4">
                <div class="card">
                    <img src="{{ $user->avatar }}" class="avatar">
                    <div class="primary">{{ $user->name }}</div>
                    <div class="inline-form">
                        <form method="post" action="/access/whitelist/{{ $user->eve_id }}">
                            {{ csrf_field() }}
                            <button type="submit">Add to whitelist</button>
                        </form>
                        <form method="post" action="/access/admin/{{ $user->eve_id }}">
                            {{ csrf_field() }}
                            <button type="submit">Make administrator</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

@endsection
