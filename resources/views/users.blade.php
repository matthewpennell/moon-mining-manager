<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Users &#0183; EVE Moon Mining Manager</title>

    </head>

    <body>

        <p>
            <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
            Welcome back, {{ $user->name }}! 
            <a href="/logout">Logout</a>
        </p>
        
        @include('common.navigation')
        
        <h1>Authorised Users</h1>

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
                        <td><img src="{{ $user->user->avatar }}" alt=""> {{ $user->user->name }}</td>
                        <td><img src="{{ $user->whitelister->avatar }}" alt=""> {{ $user->whitelister->name }}</td>
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
        
    </body>

</html>