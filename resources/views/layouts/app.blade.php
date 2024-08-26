<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <div id="bar" class="w-100 d-flex justify-content-between">
        <div>
            <button id="hamburger_button" class="btn btn-outline-secondary">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <div>
        </div>
    </div>
    <div class="wrapper d-flex h-100" id="app">
        
        <div id="side-bar-container">
            
            <nav id="sidebar" hx-boost="true" class="h-100">
                <div class="text-center" style="padding-right:2px">
                    <button id="close_nav_menu" class="btn w-100 btn-outline-secondary">
                        <i class="bi bi-arrow-left-square"></i> Close
                    </button>
                </div>
                <div class="pe-3">
                    <div class="text-center mb-3 mt-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="#ffffff" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                        </svg>
                        <div class="text-center mt-3" style="color:#ffffff">
                            {{Auth::user()->name}}
                        </div>
                    </div>

                    <a class="d-none" hx-select="#content" hx-target="#main" href="/roles" id="#__nav_helper"></a>
                    
                    <ul class="list-unstyled">
                        <li class="menu-item">
                            <div class="nav-item">
                                <a href="/home" hx-select="#content" hx-target="#main">Dashboard</a>
                            </div>
                        </li>
                        
                        <li class="menu-item">
                            <div class="nav-item">
                                <a href="/projects" hx-select="#content" hx-target="#main">Projects</a>
                            </div>          
                        </li>
                        
                        <li class="menu-item">
                            <div class="nav-item">
                                <a href="#" class="inactive-nav-item">Request</a>
                            </div>
                            <ul class="list-unstyled menu-sub-items">
                                <li class="nav-sub-item">
                                    <a href="/material_quantity_requests" hx-select="#content" hx-target="#main">Material Request</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/material_canvass" hx-select="#content" hx-target="#main">Material Canvass</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/purchase_orders" hx-select="#content" hx-target="#main">Purchase Order</a>
                                </li>
                            </ul>
                            
                        <li>

                        <li class="menu-item">
                            <div class="nav-item">
                                <a href="#" class="inactive-nav-item">Review</a>
                            </div>

                            <ul class="list-unstyled menu-sub-items">
                                <li class="nav-sub-item">
                                    <a href="/review/components" hx-select="#content" hx-target="#main">Components</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/review/material_quantity_requests" hx-select="#content" hx-target="#main">Material Request</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/review/material_canvass" hx-select="#content" hx-target="#main">Material Canvass</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/review/purchase_orders" hx-select="#content" hx-target="#main">Purchase Orders</a>
                                </li>
                            </ul>
                        </li>
                        
                    
                        <li class="menu-item">
                            <div class="nav-item">
                                <a href="#" class="inactive-nav-item">Users</a>
                            </div>
                            <ul class="list-unstyled menu-sub-items">
                            
                                <li class="nav-sub-item">
                                    <a href="/users" hx-select="#content" hx-target="#main">List</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/user/create" hx-select="#content" hx-target="#main">Create</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/access_codes" hx-select="#content" hx-target="#main">Access Codes</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/roles" hx-select="#content" hx-target="#main">Roles</a>
                                </li>
                            </ul>
                        </li>
                        <li class="menu-item">
                            <div class="nav-item">
                                <a href="#" class="inactive-nav-item">Master Data</a>
                            </div>
                            <ul class="list-unstyled menu-sub-items">
                                <li class="nav-sub-item">
                                    <a href="/master_data/material/groups" hx-select="#content" hx-target="#main">Material Groups</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/master_data/material/items" hx-select="#content" hx-target="#main">Material Items</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/master_data/payment_terms" hx-select="#content" hx-target="#main">Payment Terms</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/master_data/suppliers" hx-select="#content" hx-target="#main">Suppliers</a>
                                </li>
                                <li class="nav-sub-item">
                                    <a href="/master_data/units" hx-select="#content" hx-target="#main">Units</a>
                                </li>
                            </ul>
                        </li>

                        <li class="menu-item">
                            <div class="nav-item">
                                <button class="btn btn-secondary w-100" hx-disinherit="*" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    Logout
                                </button>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </u>
                </div>
            </nav>
        </div>
   
        <main class="w-100">
            <div class="m-3" id="main">
                @yield('content')
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

        const hamburger_button      = $q('#hamburger_button').first();
        const side_bar_container    = $q('#side-bar-container').first();
        const close_nav_menu        = $q('#close_nav_menu').first();

        if(hamburger_button){
            hamburger_button.onclick = ()=>{
                side_bar_container.style.display    = 'block';
                hamburger_button.style.display      = 'none';
            }
        }

        if(close_nav_menu){
            close_nav_menu.onclick = ()=>{
                side_bar_container.style.display    = 'none';
                hamburger_button.style.display      = 'block';
            }
        }

        $q('.nav-item > a').apply(el=>{

            el.state = 0;

            el.onclick = (e)=>{

                e.preventDefault();

                let href = el.getAttribute('href');

                if(href != '#'){
                    document.location.href = href; 
                    return false;    
                }

                let parent  = el.parentElement.parentElement;
                let ul      = parent.querySelector('.menu-sub-items');
                let count   = ul.children.length;
                
                

                if(el.state == 0){
                    ul.style.height = (50 * count)+'px';
                    el.state = 1;
                    el.classList.remove('inactive-nav-item');
                    el.classList.add('active-nav-item');
                }else{
                    ul.style.height = '0px';
                    el.state = 0;
                    
                    el.classList.remove('active-nav-item');
                    el.classList.add('inactive-nav-item');
                }

            }
        });


        $q('.nav-sub-item > a').apply(el=>{

            el.onclick = ()=>{

                $q('.selected-nav-item').items().map(item=>{
                    item.classList.remove('selected-nav-item');
                });

                el.parentElement.classList.add('selected-nav-item');

                setTimeout(()=>{
                    window.scrollTo(0,0);
                },1000);
                
            }
        });


        //Detect htmx erros
        htmx.on("htmx:beforeOnLoad", function (event) {
            const xhr = event.detail;
            
            //Catch logout scenario
            if(xhr.pathInfo.responsePath == '/login'){  
               
                event.stopPropagation() // Tell htmx not to process these requests
                document.location.href = '/login';
                return false;
            }

            if (xhr.status == 500 || xhr.status == 404) {
                event.stopPropagation() // Tell htmx not to process these requests
                window.util.alert('Error','Something went wrong ('+xhr.status+')');
                return false;
            }
        });
    </script>
</body>
</html>
