<!doctype html>

<html lang="{{ app()->getLocale() }}">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title') &#0183; Moon Mining Manager</title>

        <link rel="stylesheet" href="/css/app.css">

    </head>

    <body>

        <div class="container">

            <div class="navigation">
                @include('common.navigation')
            </div>

            <div class="content">
                <div class="col-12 header">
                    <div class="title">
                        @yield('title')
                    </div>
                </div>
                @yield('content')
            </div>

        </div>

        <script src="/js/app.js"></script>

    </body>

</html>