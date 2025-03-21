@extends('layouts.app')

@section('content')
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
        <iframe id="overview_iframe" class="w-100 h-100 d-inline-block" src="/review/material_budget/overview/{{$section_id}}"></iframe>
    </div>
  
</div>

<script type="module">
    import {$q} from '/adarna.js';
    
    const overview_iframe = $q('#overview_iframe').first();
    const fullScreenBtn   = $q('#fullScreenBtn').first();

    fullScreenBtn.onclick = ()=>{
        makeFullScreen();
    }

    function requestFullScreen(element) {
        // Supports most browsers and their versions.
        let requestMethod = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullscreen;

        requestMethod.call(element);
      
    }

    function makeFullScreen() {
        
        overview_iframe.style.position = 'absolute';
        overview_iframe.style.top  = 0;
        overview_iframe.style.left = 0;

        requestFullScreen(document.body);
    }
</script>
</div>
@endsection