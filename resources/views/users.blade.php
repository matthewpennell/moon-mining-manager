@extends('layouts.master')

@section('title', 'Home')

@section('content')
        
    <h2>Authorised Users</h2>

    <p><a href="/access/new">Add new user</a></p>

    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Added by</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($whitelisted_users as $user)
                <tr>
                    <td><img src="{{ $user->user->avatar }}" alt="" class="avatar"> {{ $user->user->name }}</td>
                    <td><img src="{{ $user->whitelister->avatar }}" alt="" class="avatar"> {{ $user->whitelister->name }}</td>
                    <td>
                        <form method="post" action="/access/blacklist/{{ $user->eve_id }}">
                            {{ csrf_field() }}
                            <button>Remove user</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
        
@endsection
