<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>DTMcode | Dashboard</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-page="dashboard" class="bg-[#edf0f5] text-slate-900 antialiased">
        <script>
            window.DTMcode = {
                user: @json(auth()->user())
            };
        </script>
        <div id="app"></div>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
    </body>
</html>
