@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/budget">
                    <span>
                       Budget
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Components
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
<hr>

    <table class="record-table-horizontal mb-3">
        <tbody>
            <tr>
                <th>Project</th>
                <td>
                    <a href="/budget/project/{{$project->id}}">{{$project->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Section</th>
                <td>
                    <a href="/budget/section/{{$section->id}}">{{$section->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Contract Item</th>
                <td>
                    {{$contract_item->name}}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Component
        </div>
        <div class="folder-form-body">
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Query</label>
                        <input type="text" id="query" class="form-control"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container" id="list"></div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';

    const list            = $q('#list').first();
    const query           = $q('#query').first();
    const showMoreBtn     = $q('#showMoreBtn').first();
  
    
    let page            = 1;
    let order           = 'ASC';
    let orderBy         = 'name';
    let fetching        = false;
    
    const t = new Template();
    
    function reinitalize(){
        page = 1;
        $el.clear(list);   
    }

    function renderRows(data){
        
        data.map(item=>{

            let row = t.div({class:'item-container fade-in'},()=>{

                t.div({class:'item-header'},item.name);
                t.div({class:'item-body'},()=>{
                    t.div({class:'row'},()=>{

                        t.div({class:'col-lg-6'},()=>{

                           //t.span( String(item.id).padStart(6,'0') );

                        });//div col

                        t.div({class:'col-lg-6'},()=>{

                            //t.span( item.status );

                        });//div col

                    });//div row
                });//div
            
            });//div

            row.onclick = ()=>{
                window.util.navTo('/budget/component/'+item.id);
            };

            $el.append(row).to(list);
            
        });

    }

    function showData(){

        window.util.blockUI();
        fetching = true;

        window.util.$get('/api/budget/component/list',{
            contract_item_id:'{{$contract_item->id}}',
            query: query.value,
            page: page,
            order: order,
            order_by: orderBy,
            limit: 10
        }).then(reply=>{

            window.util.unblockUI();
            fetching = false;        

            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };

            page++;

            if(reply.data.length){
                renderRows(reply.data); 
            }else{
                showMoreBtn.style.display = 'none';
            }
            
        });
    }
    
    function runQuery(){
        showMoreBtn.style.display = 'block';
        reinitalize();
        showData();
    }

    query.onkeypress = (e)=>{
        if (e.key === "Enter") {
            runQuery();
        }
    }

    query.onkeyup = (e)=>{
        if ((query.value.length >= 4 || query.value == '') && fetching == false) {
            runQuery();
        }
    }


    showMoreBtn.onclick = ()=>{
        showData();
    }




    reinitalize();
    showData();
</script>
</div>
@endsection