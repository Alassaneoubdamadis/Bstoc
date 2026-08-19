<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ platform_app_name() }}</title>
    @if(platform_favicon_url())
        <link rel="icon" href="{{ platform_favicon_url() }}">
    @endif
        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"/>
    </head>
    <body class="antialiased">
    <div id="root"></div>
    </body>
<script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
</html>
