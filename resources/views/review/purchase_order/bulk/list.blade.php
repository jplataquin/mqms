@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/purchase_orders">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Purchase Orders
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    
  
    <div id="result"></div>   
 
</div>
<script type="module">
    import {$q,Template,$el} from '/adarna.js';


    ///review/bulk/purchase_order/list'
    

        window.util.$get('/api/review/bulk/purchase_order/list',{}).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };

            console.log(reply.data);
        });
</script>
</div>
@endsection