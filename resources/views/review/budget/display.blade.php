@extends('layouts.app')

@section('content')

<div id="content">
    <style>

        .fullscreen{
            position:fixed;
            top:0;
            left:0;
            z-index:1000;
        }

        .no-scroll{
            height:100%;
            overflow:hidden;
        }
        
    </style>

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
                            Budget
                        </span>
                        <i class="ms-2 bi bi-display"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div style="height: 100vh">
            <div class="text-end mb-5">
                <button class="btn btn-secondary" id="fullScreenBtn">Full Screen</button>
            </div>
            <iframe id="sheet_iframe" class="w-100 h-100 d-inline-block" src="/review/budget/sheet/{{$section_id}}"></iframe>
        </div>
    
    </div>

    <script type="module">
        import {$q} from '/adarna.js';
        
        const sheet_iframe      = $q('#sheet_iframe').first();
        const fullScreenBtn     = $q('#fullScreenBtn').first();
        
        document.body.classList.remove('no-scroll');
        sheet_iframe.classList.remove('fullscreen');

        window.getSheetPos = ()=>{
            let data = sheet_iframe.getBoundingClientRect();
            return data;
        };

        window.reloadIframe = ()=>{
            overview_iframe.contentWindow.location.reload();
        }

        window.exitFullscreen = ()=>{
            sheet_iframe.classList.remove('fullscreen');
            document.body.classList.remove('no-scroll');
        }

        fullScreenBtn.onclick = ()=>{
            
            sheet_iframe.classList.add('fullscreen');
            document.body.classList.add('no-scroll');
        }
        

        window.util.quickNav = {
            title:'Review Budget',
            url: '/review/budget'
        };
        
        
    </script>
</div>
@endsection