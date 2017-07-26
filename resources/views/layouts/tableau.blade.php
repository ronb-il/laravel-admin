<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Personali Cockpit</title>
</head>
<body>
    @yield('content')
    <script src="{{ url('scripts/frontend.js') }}"></script>
    <script src="{{ url('scripts/jquery.url.js') }}"></script>
    <script src="{{ url('scripts/jquery.base64.js') }}"></script>
    @yield('custom-javascript')
</body>
</html>
