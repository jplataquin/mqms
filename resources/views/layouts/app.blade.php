<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="wrapper d-flex align-items-stretch" id="app">

    <nav id="sidebar">
        <div class="custom-menu">
        <button type="button" id="sidebarCollapse" class="btn btn-primary">
        <i class="fa fa-bars"></i>
        <span class="sr-only">Toggle Menu</span>
        </button>
        </div>
        <div class="p-4 pt-5">
        <h1><a href="index.html" class="logo">Splash</a></h1>
        <ul class="list-unstyled components mb-5">
        <li class="active">
        <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Home</a>
        <ul class="collapse list-unstyled" id="homeSubmenu">
        <li>
        <a href="#">Home 1</a>
        </li>
        <li>
        <a href="#">Home 2</a>
        </li>
        <li>
        <a href="#">Home 3</a>
        </li>
        </ul>
        </li>
        <li>
        <a href="#">About</a>
        </li>
        <li>
        <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Pages</a>
        <ul class="collapse list-unstyled" id="pageSubmenu">
        <li>
        <a href="#">Page 1</a>
        </li>
        <li>
        <a href="#">Page 2</a>
        </li>
        <li>
        <a href="#">Page 3</a>
        </li>
        </ul>
        </li>
        <li>
        <a href="#">Portfolio</a>
        </li>
        <li>
        <a href="#">Contact</a>
        </li>
        </ul>
        <div class="mb-5">
        <h3 class="h6">Subscribe for newsletter</h3>
        <form action="#" class="colorlib-subscribe-form">
        <div class="form-group d-flex">
        <div class="icon"><span class="icon-paper-plane"></span></div>
        <input type="text" class="form-control" placeholder="Enter Email Address">
        </div>
        </form>
        </div>
        <div class="footer">
        <p>
        Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="icon-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib.com</a>
        </p>
        </div>
        </div>
    </nav>

        <main id="content" class="p-4 p-md-5 pt-5">
            @yield('content')
        </main>

    </div>

    

    <div id="primary_modal" class="modal" tabindex="-1">
        <div class="modal-dialog">
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
</body>
</html>
