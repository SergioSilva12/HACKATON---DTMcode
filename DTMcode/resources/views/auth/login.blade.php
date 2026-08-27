<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>DTMcode | Login</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-page="auth" data-auth-mode="login" class="antialiased">
        @php
            $authData = [
                'mode' => 'login',
                'csrf' => csrf_token(),
                'errors' => $errors->all(),
            ];
        @endphp
        <script>
            window.DTMAuth = {!! json_encode($authData) !!};
        </script>
        <div id="app"></div>
    </body>
</html>
