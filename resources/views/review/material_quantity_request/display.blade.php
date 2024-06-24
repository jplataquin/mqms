@extends('layouts.app')

@section('content')
<div class="container">
<h5>Review » Material Quantity Request » Display</h5>
<hr>
    <table class="table">
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
                <th>Description</th>
                <td>
                    <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div id="itemContainer"></div>
    <div>
        <button class="btn btn-warning w-100 mt-3 d-none" id="addBtn">Add More</button>
    </div>
    <div class="row mt-5">
        <div class="col-6">
            @if($material_quantity_request->status == 'PEND')
                <button class="btn btn-danger" id="disapproveBtn">Disapprove</button>
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
    
    request_items.map(request_item => {

        count++;

        let itemForm = RequestMaterialItem({
            id                  : request_item.id,               
            componentId         : '{{$component->id}}',
            componentItemList   : @json($componentItem_options),
            materialList        : @json($material_options),
            componentItemId     : request_item.component_item_id,
            materialItemId      : request_item.material_item_id,
            totalRequested      : '',
            requestedQuantity   : request_item.requested_quantity,
            editable            : false
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
        document.location.href = '/review/material_quantity_requests';
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
        let disapproveBtn = $q('#disapproveBtn').first();

        approveBtn.onclick = (e)=>{
            e.preventDefault();

            if(!confirm('Are you sure you want to APPROVE this?')){
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/review/material_quantity_request/approve',{
                id: '{{$material_quantity_request->id}}'
            }).then(reply=>{

                if(reply.status <= 0 ){
                    window.util.unblockUI();
                    alert(reply.message);
                    return false;
                };

                window.util.unblockUI();

                document.location.href = '/review/material_quantity_requests/';

            });
        }

        disapproveBtn.onclick = (e)=>{
            e.preventDefault();

            if(!confirm('Are you sure you want to DISAPPROVE this?')){
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/review/material_quantity_request/disapprove',{
                id: '{{$material_quantity_request->id}}'
            }).then(reply=>{

                if(reply.status <= 0 ){
                    window.util.unblockUI();
                    alert(reply.message);
                    return false;
                };

                window.util.unblockUI();

                document.location.href = '/review/material_quantity_requests/';

            });
        }
        function formValidation(){

            return true;
        }

      

    @endif
</script>

@endsection