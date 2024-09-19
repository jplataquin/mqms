@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/material_quantity_requests">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Material Request
                    </span>
                    
                    <i class="ms-2 bi bi-display"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>
    <div class="folder-form-container">
        <div class="folder-form-tab">
            Material Request Review
        </div>
        <div class="folder-form-body">
            <table class="record-table-horizontal">
                <tbody>
                    <tr>
                        <th width="150px">ID</th>
                        <td>{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <th>Project</th>
                        <td>{{$project->name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{$section->name}}</td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>{{$component->name}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$material_quantity_request->status}}</td>
                    </tr>
                    <tr>
                        <th>Requested By</th>
                        <td>{{$material_quantity_request->CreatedByUser()->name}}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>
                            <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
    <div id="itemContainer"></div>
    <div>
        <button class="btn btn-warning w-100 mt-3 d-none" id="addBtn">Add More</button>
    </div>
    <div class="row mt-5">
        <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">
            @if($material_quantity_request->status == 'PEND')
                <button class="btn btn-danger" id="rejectBtn">Reject</button>
            @endif

            @if($material_quantity_request->status == 'PEND')
                <button class="btn btn-warning" id="approveBtn">Approve</button>
            @endif

            <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
            
        </div>
    </div>

</div>




<script type="module">
    import {$q,$el} from '/adarna.js';
    import RequestMaterialItem from '/ui_components/RequestMaterialItem.js';

    let itemContainer = $q('#itemContainer').first();
    let cancelBtn     = $q('#cancelBtn').first();
    let description   = $q('#description').first();
    let request_items = @json($request_items);
    let count         = 0;
    let deleteItems   = [];
    
    const unit_options              = @json($unit_options);
    const component_item_options    = @json($componentItem_options);
    const material_options          = @json($material_options);

    window.util.quickNav = {
        title:'Review Material Request',
        url: '/review/material_quantity_request'
    };
    
    request_items.map(request_item => {

        count++;

        let itemForm = RequestMaterialItem({
            id                  : request_item.id,               
            componentId         : '{{$component->id}}',
            componentItemList   : component_item_options,
            materialList        : material_options,
            componentItemId     : request_item.component_item_id,
            materialItemId      : request_item.material_item_id,
            requestedQuantity   : request_item.requested_quantity,
            editable            : false,
            unitOptions         : unit_options
        });

        itemForm.handler.deleteCallback((dom)=>{
            
            if(count == 1){
                alert('At least one item must remain');
                return false;
            }

            if(confirm('Are you sure you want to delete this item?')){

                deleteItems.push(dom.comp.getModel().id);
                $el.remove(dom);
            }

            

            count--;

        });

        itemForm.handler.updateApprovedQuantity();
        
        $el.append(itemForm).to(itemContainer);

    });


    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/review/material_quantity_requests');
    }

    function setIndexNumber(){
        
        let i = 1;

        $q('.items').apply(item=>{

            item.handler.setIndexNumber(i);
            i++;
        });
    }


    setIndexNumber();

    @if($material_quantity_request->status == 'PEND')

        let approveBtn  = $q('#approveBtn').first();
        let rejectBtn = $q('#rejectBtn').first();

        approveBtn.onclick = async (e)=>{
            e.preventDefault();

            if(! await window.util.confirm('Are you sure you want to APPROVE this record?')){
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/review/material_quantity_request/approve',{
                id: '{{$material_quantity_request->id}}'
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0 ){
                    
                    window.util.showMsg(reply);
                    return false;
                };


                window.util.navTo('/review/material_quantity_requests/');

            });
        }

        rejectBtn.onclick = async (e)=>{
            e.preventDefault();

            if(! await window.util.confirm('Are you sure you want to REJECT this?')){
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/review/material_quantity_request/reject',{
                id: '{{$material_quantity_request->id}}'
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0 ){
                    
                    window.util.showMsg(reply);
                    return false;
                };

                window.util.navTo('/review/material_quantity_requests/');

            });
        }
        
        function formValidation(){

            return true;
        }

      

    @endif
</script>
</div>
@endsection