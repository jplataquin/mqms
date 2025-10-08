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
                       Bulk PO
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    
  
    <div id="result_container"></div>   
 
</div>
<script type="module">
    import {$q,Template,$el} from '/adarna.js';


    const result_container = $q('#result_container').first();

    const t = new Template();

    window.util.$get('/api/review/bulk/purchase_order/list',{}).then(reply=>{

        
        window.util.unblockUI();

        if(reply.status <= 0 ){
            
            window.util.showMsg(reply);
            return false;
        };

        let result      = reply.data.result;
        let projects    = reply.data.projects;

        for(let project_id in result){

            let items = result[project_id];

            const project_div = t.div({class:'mb-3'},()=>{
                t.h3( projects[project_id].name );

                items.map(item => {

                    if(item.flag){
                    
                        t.div({class:'row'}=>{
                            t.div({class:'col-11'},()=>{
                                t.span({class:'text-success'},'[ok]');
                                t.txt(item.po.id);
                            });
                            t.div({class:'col-1 text-end'},()=>{
                                t.input({class:'po form-control', value:item.po.id, checked:true, type:'checkbox'});
                            });
                        });
                    
                    }else{

                         t.div({class:'row'}=>{
                            t.div({class:'col-11'},()=>{
                                t.span({class:'text-danger'},'[invalid]');
                                t.txt(item.po.id);
                            });
                            t.div({class:'col-1 text-end'},()=>{
                                t.input({class:'po form-control', value:item.po.id, checked:false, type:'checkbox'});
                            });
                        });
                    }
                });
            });


            result_container.appendChild(project_div);
        }
    });
</script>
</div>
@endsection