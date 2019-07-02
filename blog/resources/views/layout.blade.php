<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>@yield('title', 'title by default')</title>

    <style type="text/css">
        #menu {
            padding: 0;
            margin: 0;
            list-style-type: none;
            font-size: 13px;
            color: #717171;
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
            color: #717171;
            text-decoration: none;
        }

        #menu a:hover {
            color: White;
        }

        .list {
            background-color:#fff;
            margin:20px auto;
            width:100%;
            max-width:500px;
            padding:20px;
            border-radius:2px;
            box-shadow:3px 3px 0 rgba(0, 0, 0, .1);
            box-sizing:border-box;
        }

        body {
            background-color: #F8F8F8;
        }
    </style>
</head>

<body>

<div class="list">
<ul id="menu">
    <li><a href="/"> home </a></li>
    <li><a href="/about"> about us </a></li>
    <li><a href="/contact"> contact </a></li>
    <li><a href="/projects"> display projects </a></li>
    <li><a href="/projects/create"> create new project </a></li>
</ul>
<br/><br/>

@yield('content')
</div>

</body>
</html>