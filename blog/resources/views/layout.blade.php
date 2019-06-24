<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'def title')</title>
</head>
<body>
<ul>
    <li><a href="/"> home </a></li>
    <li><a href="/about"> about </a></li>
    <li><a href="/contact"> contact </a></li>
</ul>

@yield('content')

</body>
</html>