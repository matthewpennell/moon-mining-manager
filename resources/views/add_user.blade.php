<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Add New Users &#0183; EVE Moon Mining Manager</title>

    </head>

    <body>

        <p>
            <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
            Welcome back, {{ $user->name }}! 
            <a href="/logout">Logout</a>
        </p>
        
        @include('common.navigation')
        
        <h1>Add New Users</h1>

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
                        <td><img src="{{ $user->avatar }}" alt=""> {{ $user->name }}</td>
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
        
    </body>

</html>