@extends('layouts.app')

@section('content')
<div id="content">

    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/coupons">
                        <span>
                        Review Coupon
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            List
                        </span>
                        
                        <i class="ms-2 bi bi-list-ul"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container">
                <div class="folder-form-tab">
                    Coupons
                </div>
                <div class="folder-form-body">
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Sort By</label>
                                <select class="form-select" id="sortSelect">
                                    <option value="1" selected>Latest Entry</option>
                                    <option value="2">Oldest Entry</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Created By</label>
                                <select class="form-select" id="createdBySelect">
                                    <option value=""> - </option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                      
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>From</label>
                                <input type="date" class="form-control" id="from"/>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>To</label>
                                <input type="date" class="form-control" id="to"/>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                            <div class="col-lg-12 text-end">    
                                

                                <button id="searchBtn" class="btn btn-primary">Search</button>

                                <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
                        
                            </div> 
                    </div>

                </div>
        </div>

        <div class="table-responsive"> 
            <table class="table border" style="width:150%">
                <thead>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Date Created</th>
                <thead>
                <tbody id="list">
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-lg-12">
                <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
            </div>
        </div>

    </div>

    <script type="module">
        import {$q,Template,$el} from '/adarna.js';

        const list                  = $q('#list').first();
        const query                 = $q('#query').first();
        const searchBtn             = $q('#searchBtn').first();
        const showMoreBtn           = $q('#showMoreBtn').first();
        
        const sortSelect            = $q('#sortSelect').first();
        const createdBySelect       = $q('#createdBySelect').first();
        const from                  = $q('#from').first();
        const to                    = $q('#to').first();
        

        let page            = 1;
        let order           = 'DESC';
        let orderBy         = 'id';
        
        const t = new Template();
        
        function reinitalize(){
            page = 1;
            $el.clear(list);   
        }

        function renderRows(data){
            
            data.map(item=>{

                let row = t.tr({class:'selectable-div'},()=>{
                   
                    t.td({},()=>{
                        t.txt( String(item.id).padStart(4,'0') );
                    });
                    t.td({},()=>{
                        t.txt(item.status);
                    });
                    t.td({},()=>{
                        t.txt(item.created_by_name);
                    });
                    t.td({},()=>{
                        t.txt(item.created_at);
                    });
                });

                row.onclick = ()=>{
                    window.util.navTo('/coupon/'+item.id);
                };

                $el.append(row).to(list);
                
            });

        }

        function showData(){

            window.util.blockUI();

            window.util.$get('/api/review/coupon/list',{
                page                : page,
                order               : order,
                order_by            : orderBy,
                created_by          : createdBySelect.value,
                from                : from.value,
                to                  : to.value,
                
                limit: 10
            }).then(reply=>{

                window.util.unblockUI();
                    

                if(reply.status <= 0){
                    
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
    
        searchBtn.onclick = ()=>{
            showMoreBtn.style.display = 'block';
            reinitalize();
            showData();
        }

        showMoreBtn.onclick = ()=>{
            showData();
        }

        sortSelect.onchange = ()=>{
            reinitalize();

            let select = parseInt(sortSelect.value);

            switch(select){
                case 1:
                    order   = 'ASC';
                    orderBy = 'id';
                    break;
                case 2:
                    order   = 'DESC';
                    orderBy = 'id';
                    break;
            }

            showData();
        }         
        
        
        reinitalize();
        showData();
    </script>

</div>
@endsection