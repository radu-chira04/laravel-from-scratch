<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title> @yield('title', 'title by default') </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="https://laravel.com/favicon.png">

    <!-- Styles custom -->
    <style type="text/css">
        #menu {
            padding: 0;
            margin: 0;
            list-style-type: none;
            font-size: 13px;
            width: 100%;
            border-right: 1px solid #bbb;
        }

        #menu li {
            border-bottom: 1px solid #eeeeee;
            padding: 7px 10px 7px 10px;
            float: left;
        }

        #menu li:hover {
            color: White;
            background-color: #ffcc00;
        }

        #menu a:link {
            color: inherit;
            text-decoration: none;
        }

        #menu a:hover {
            color: White;
        }

        .line-through{
            text-decoration: line-through;
        }

        .list {
            background-color: #fff;
            margin: 20px auto;
            width: 100%;
            max-width: 500px;
            padding: 20px;
            border-radius: 2px;
            box-shadow: 3px 3px 0 rgba(0, 0, 0, .1);
            box-sizing: border-box;
        }
    </style>
</head>

<body style="background-color: #F8F8F8;">

@include('navbar')

<div class="list">
    <ul id="menu">
        <li><a href="/"> home </a></li>
        <li><a href="/about"> about us </a></li>
        <li><a href="/contact"> contact </a></li>
        <li><a href="/projects"> display projects </a></li>
        <li><a href="/projects/create"> new project </a></li>
        <li><a href="/tasks/create"> new task </a></li>
    </ul>
    <br/><br/>
    @yield('content')
</div>

</body>
</html>