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

    <div>
        <iframe class="w-100 h-100 d-inline-block" src="/review/material_budget/overview/{{$section_id}}"></iframe>
    </div>
  
</div>

<script type="module">
    //import {$q,$el,Template} from '/adarna.js';
  
</script>
</div>
@endsection