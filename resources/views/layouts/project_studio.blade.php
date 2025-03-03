<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Zen+Kaku+Gothic+New&display=swap" rel="stylesheet">

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://unpkg.com/htmx.org@2.0.0"></script>
    
</head>
<body>

<div class="wrapper d-flex h-100" id="app">
           
        <main class="w-100">
            <div>
                <div id="main">
                    @yield('content')
                </div>
                
                <div class="drawer_modal_background"></div>
                <div class="drawer_modal">
                    <div class="drawer_modal_header pe-3 ps-3 d-flex justify-content-between align-items-stretch">
                        <div class="p-2">
                            <h5 class="drawer_modal_title"></h5>
                        </div>
                        <div class="p-2">
                            <button type="button" onclick="window.util.drawerModal.close()" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="drawer_modal_body p-3">
                    </div>
                </div>
                
            </div>
        </main>

    </div>

    <div id="primary_modal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="primary_modal_title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="primary_modal_body"></div>
                <div class="modal-footer" id="primary_modal_footer"></div>
            </div>
        </div>
    </div>

    

    <script type="module">
        import {$q} from '/adarna.js';

       
    </script>
</body>
</html>