<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'def title')</title>

    <style type="text/css">
        #Menu
        {
            padding:0;
            margin:0;
            list-style-type:none;
            font-size:13px;
            color:#717171;
            width:100%;
            border-right: 1px solid #bbb;
        }

        #Menu li
        {
            border-bottom:1px solid #eeeeee;
            padding:7px 10px 7px 10px;
            float: left;
        }

        #Menu li:hover
        {
            color:White;
            background-color:#ffcc00;
        }

        #Menu a:link
        {
            color:#717171;
            text-decoration:none;
        }

        #Menu a:hover
        {
            color:White;
        }
    </style>
</head>

<body>
<ul id="Menu" >
    <li><a href="/"> home </a></li>
    <li><a href="/about"> about us </a></li>
    <li><a href="/contact"> contact </a></li>
</ul><br/><br/>

@yield('content')

</body>
</html>