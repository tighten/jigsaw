<!DOCTYPE html>
<html lang="{{ $page->lang }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="{{ $page->description }}">
        <meta name="keywords" content="{{ $page->keywords }}">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <link rel="stylesheet" href="/css/main.css">
        <title> {{ $page->title }}</title>
    </head>
    <body>
        @yield('body')
    </body>
</html>
