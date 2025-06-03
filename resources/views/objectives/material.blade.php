@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/objectives/material">
                        <span>
                        Objectives
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            Material
                        </span>
                        <i class="ms-2 bi bi-display"></i>
                    </a>
                </li>
            </ul>
        </div>
    <hr>


    
    </div>

<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    function showData(){
        window.util.blockUI();

        window.util.$post('/api/objectives/material',{
        
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            console.log(reply.data);
        });
    }


    showData();
    
</script>
</div>
@endsection