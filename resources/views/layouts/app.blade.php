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
    <div id="bar" class="w-100">
    </div>
    <div class="wrapper d-flex align-items-stretch" id="app">
        <nav id="sidebar">
            <div>
                <button>Hide</button>
            </div>
            <ul class="list-unstyled">
                <li class="menu-item">
                    <div class="nav-item">Item 1</div>

                    <ul class="list-unstyled menu-sub-items">
                        <li class="nav-sub-item">Item 1.1</li>
                        <li class="nav-sub-item">Item 1.2</li>
                        <li class="nav-sub-item">Item 1.3</li>
                    </ul>
                </li>
                <li class="menu-item">
                    <div class="nav-item">Item 2</div>
                </li>
                <li class="menu-item">
                    <div class="nav-item">Item 3</div>
                    
                </li> 
            </u>
        </nav>
   
        <main id="content" class="w-100 container">
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

    <script type="module">
        import {$q} from '/adarna.js';

        $q('.nav-item').apply(el=>{

            el.onclick = (e)=>{

                let parent = el.parentElement;
                let ul = parent.querySelector('.menu-sub-items');

                ul.style.height = 'auto';
            }
        });
    </script>
</body>
</html>
