@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/material_quantity_requests">
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
    <div class="folder-form-container mb-5">
        <div class="folder-form-tab">
            Material Request
        </div>
        <div class="folder-form-body">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Project</label>
                        <input type="text" value="{{$project->name}}" class="form-control" disabled="true"/>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" value="{{$section->name}}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>
            <div class="row mb-3">

            
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Contract Item</label>
                        <input type="text" value="{{$contract_item->item_code}} - {{$contract_item->description}}" class="form-control" disabled="true"/>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Component</label>
                        <input type="text" value="{{$component->name}}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>

            
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>ID No.</label>
                        <input type="text" value="{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" value="{{$material_quantity_request->status}}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>

            
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Date Created</label>
                        <input type="text" value="{{ $material_quantity_request->created_at }}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>

            @if($material_quantity_request->status == 'PEND')
            <div class="row mb-3">
                <div class="col-lg-12 text-end">
                    <button class="btn btn-outline-primary" id="reviewLinkBtn">
                        Review Link
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <div id="itemContainer"></div>
    <div>
        <button class="btn btn-warning w-100 mt-3 d-none" id="addBtn">Add More</button>
    </div>


    <div class="form-container mt-5">
        <div class="form-header">
            &nbsp;
        </div>
        <div class="form-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea id="description" class="form-control" disabled="true">{{$material_quantity_request->description}}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mt-5 mb-3">
        <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">

            <button class="btn-primary btn" id="showPOBtn">PO List</button>
            <button class="btn-warning btn" id="printBtn">Print</button>
            
            @if($material_quantity_request->status == 'PEND')
                <button class="btn btn-primary" id="editBtn">Edit</button>
                <button class="btn btn-warning d-none" id="updateBtn">Update</button>
            @endif
            
            @if($material_quantity_request->status == 'APRV')
                <button class="btn btn-danger" id="revertPendBtn">Revert to Pending</button>
            @endif

            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>

        </div>
    </div>

</div>

<script type="module">
    import {$q,$el} from '/adarna.js';
    import RequestMaterialItem from '/ui_components/RequestMaterialItem.js';

    const itemContainer = $q('#itemContainer').first();
    const addBtn        = $q('#addBtn').first();
    const cancelBtn     = $q('#cancelBtn').first();
    const description   = $q('#description').first();
    const printBtn      = $q('#printBtn').first();
    const revertPendBtn = $q('#revertPendBtn').first();
    const reviewLinkBtn = $q('#reviewLinkBtn').first();

      
    let count         = 0;
    let deleteItems   = [];
    
    const request_items             = @json($request_items);
    const unit_options              = @json($unit_options);
    const component_item_options    = @json($component_item_options);
    const material_options          = @json($material_options);
    
    window.util.quickNav = {
        title:'Material Request',
        url:'/material_quantity_request'
    };

    if(reviewLinkBtn){
        reviewLinkBtn.onclick = async ()=>{
            let test = await window.util.copyToClipboard('{{ url("/review/material_quantity_request/".$material_quantity_request->id); }}');
            if(test){
                alert('Review Link for "Material Request: {{$material_quantity_request->id}}" copied!');
            }else{
                alert('Failed to copy');
            }
        }
    }
    
    if(revertPendBtn){
       
        revertPendBtn.onclick = async (e)=>{
            
            if(! await window.util.confirm('Are you sure you want to rever this request to PENDING status?')){
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/material_quantity_request/revert_to_pending',{
                id:'{{$material_quantity_request->id}}'
            }).then(reply=>{
                
                window.util.unblockUI();

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }

                window.util.navReload();
            });
        }

    }//if

    printBtn.onclick = (e)=>{
        window.open('/material_quantity_request/print/{{$material_quantity_request->id}}','_blank').focus();
    }

    request_items.map(request_item => {

        count++;

        let itemForm = RequestMaterialItem({
            id                     : request_item.id,               
            componentId            : '{{$component->id}}',
            componentItemList      : component_item_options,
            materialList           : material_options,
            componentItemId        : request_item.component_item_id,
            materialItemId         : request_item.material_item_id,
            prevApprovedQuantity   : '',
            requestedQuantity      : request_item.requested_quantity,
            editable               : false,
            unitOptions            : unit_options
        });

        itemForm.handler.deleteCallback(async (dom)=>{
            
            if(count == 1){
                alert('At least one item must remain');
                return false;
            }

            if(await window.util.confirm('Are you sure you want to delete this item?')){
                deleteItems.push(dom.handler.getValues().id);
                $el.remove(dom);
            }

            

            count--;

        });

        itemForm.handler.updateApprovedQuantity();

        $el.append(itemForm).to(itemContainer);

    });

    addBtn.onclick = (e)=>{
        e.preventDefault();

        if(count >= 6){
            window.util.alert('Error','Maximum of 6 items per request');
            return false;
        }

        let item = RequestMaterialItem({
            componentId:        '{{$component->id}}',
            componentItemList:  @json($component_item_options),
            materialList:       @json($material_options)
        });

        item.handler.deleteCallback(async (dom)=>{
                
            if(count == 1){
                alert('At least one item must remain');
                return false;
            }

            if(await window.util.confirm('Are you sure you want to delete this item?')){
                $el.remove(dom);
                setTimeout(()=>{
                    setIndexNumber();
                },50);
            }

            count--;

        });

        $el.append(item).to(itemContainer);

        count++;

        setIndexNumber();
    }

    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/material_quantity_requests');
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

        let editBtn       = $q('#editBtn').first();
        let updateBtn     = $q('#updateBtn').first();

        editBtn.onclick = (e)=>{
            e.preventDefault();

            $q('.items').apply(item=>{

                item.handler.editable(true);
                item.handler.updateApprovedQuantity();
            });

            description.disabled = false;

            editBtn.classList.add('d-none');
            updateBtn.classList.remove('d-none');
            addBtn.classList.remove('d-none');

            cancelBtn.onclick = (e)=>{
                e.preventDefault();
                window.util.navReload();
            }
        }

        
        function formValidation(){

            return true;
        }

        updateBtn.onclick = (e)=>{
            e.preventDefault();

            if(!formValidation()){
                return false;
            }

            window.util.blockUI();

            let items = [];

            let itemsEl = $q('.items').items();

            itemsEl.map(el =>{
                items.push(el.handler.getValues());
            });

            window.util.$post('/api/material_quantity_request/update',{
                id: '{{$material_quantity_request->id}}',
                description: description.value,
                items:JSON.stringify(items),
                delete_items: JSON.stringify(deleteItems)
            }).then(reply=>{
                
                window.util.unblockUI();

                if(reply.status <= 0 ){
                    window.util.showMsg(reply);
                    return false;
                };

                window.util.navTo('/material_quantity_request/'+reply.data.id);

            });
        }

    @endif
</script>
</div>
@endsection