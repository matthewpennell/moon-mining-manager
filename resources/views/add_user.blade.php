@extends('layouts.master')

@section('title', 'New User')

@section('content')
        
    <h2>Add New Users</h2>

    <p>These are all the users that have attempted to access the application in the past. Click the button to whitelist your chosen user(s).</p>

    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Whitelist</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($access_history as $user)
                <tr>
                    <td><img src="{{ $user->avatar }}" alt="" class="avatar"> {{ $user->name }}</td>
                    <td>
                        <form method="post" action="/access/whitelist/{{ $user->eve_id }}">
                            {{ csrf_field() }}
                            <button>Whitelist user</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
        
@endsection
