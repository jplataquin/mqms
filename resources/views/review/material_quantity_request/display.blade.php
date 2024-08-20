@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="#">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="/review/material_requests">
                    <span>
                       Material Requests
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Display
                    </span>	
                    <i class="ms-2 bi bi-display"></i>	
                </a>
            </li>
        </ul>
    </div>
<hr>
    <table class="record-table">
        <tr>
            <th>test 1</th>
            <th>test 2</th>
        </tr>
        <tr>
            <td>asdad 1</td>
            <td>asdad 2</td>
        </tr>
    </table>
    <div class="folder-form-container">
        <div class="folder-form-tab">
            Material Request Review
        </div>
        <div class="folder-form-body">
            <table class="record-table">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{$material_quantity_request->id}}</td>
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
        <div class="col-6">
            @if($material_quantity_request->status == 'PEND')
                <button class="btn btn-danger" id="rejectBtn">Reject</button>
            @endif
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>

            @if($material_quantity_request->status == 'PEND')
                <button class="btn btn-warning" id="approveBtn">Approve</button>
            @endif
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

    request_items.map(request_item => {

        count++;

        let itemForm = RequestMaterialItem({
            id                  : request_item.id,               
            componentId         : '{{$component->id}}',
            componentItemList   : component_item_options,
            materialList        : material_options,
            componentItemId     : request_item.component_item_id,
            materialItemId      : request_item.material_item_id,
            totalRequested      : '',
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

        approveBtn.onclick = (e)=>{
            e.preventDefault();

            if(!confirm('Are you sure you want to APPROVE this?')){
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

        rejectBtn.onclick = (e)=>{
            e.preventDefault();

            if(!confirm('Are you sure you want to REJECT this?')){
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