@extends('layouts.app')

@section('content')
<style>
    .fullscreen{
        position:absolute;
        top:0;
        left:0;
        z-index:900;
    }
        
</style>
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/material_canvass">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Material Budget
                    </span>
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div style="height: 100vh">
         <div class="text-end">
            <button class="btn btn-secondary" id="fullScreenBtn">Full Screen</button>
        </div>
        <iframe id="overview_iframe" class="w-100 h-100 d-inline-block" src="/review/budget/sheet/{{$section_id}}"></iframe>
    </div>
  
</div>

<script type="module">
    import {$q} from '/adarna.js';
    
    const overview_iframe = $q('#overview_iframe').first();
    const fullScreenBtn   = $q('#fullScreenBtn').first();

    window.reloadIframe = ()=>{
        overview_iframe.contentWindow.location.reload();
    }

    fullScreenBtn.onclick = ()=>{
        makeFullScreen();
    }

    
    document.onfullscreenchange = (e) => {
       
        if (document.fullscreenElement) {
           
            overview_iframe.classList.add('fullscreen');
        } else {
            overview_iframe.classList.remove('fullscreen');
        }
    };

    window.util.quickNav = {
        title:'Review Budget',
        url: '/review/budget'
    };
    
    function requestFullScreen(element) {
        // Supports most browsers and their versions.
        let requestMethod = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullscreen;

        requestMethod.call(element);
      
    }

    function makeFullScreen() {
        requestFullScreen(document.body);
    }
</script>
</div>
@endsection