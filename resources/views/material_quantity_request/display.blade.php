@extends('layouts.app')

@section('content')
<div class="container">
<h5>Material Quantity Request » Display</h5>
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
        <div class="col-12 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>

            @if($material_quantity_request->status == 'PEND')
                <button class="btn btn-primary" id="editBtn">Edit</button>
                <button class="btn btn-warning d-none" id="updateBtn">Update</button>
            @endif
        </div>
    </div>

</div>

<script type="module">
    import {$q,$el} from '/adarna.js';
    import RequestMaterialItem from '/ui_components/RequestMaterialItem.js';

    let itemContainer = $q('#itemContainer').first();
    let addBtn        = $q('#addBtn').first();
    let cancelBtn     = $q('#cancelBtn').first();
    let description   = $q('#description').first();
    let request_items = @json($request_items);
    let count         = 0;
    let deleteItems   = [];
    
    request_items.map(request_item => {

        count++;

        let itemForm = RequestMaterialItem({
            id                     : request_item.id,               
            componentId            : '{{$component->id}}',
            componentItemList      : @json($componentItem_options),
            materialList           : @json($material_options),
            componentItemId        : request_item.component_item_id,
            materialItemId         : request_item.material_item_id,
            prevApprovedQuantity   : '',
            requestedQuantity      : request_item.requested_quantity,
            editable               : false
        });

        itemForm.handler.deleteCallback((dom)=>{
            
            if(count == 1){
                alert('At least one item must remain');
                return false;
            }

            if(confirm('Are you sure you want to delete this item?')){
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
            alert('Maximum of 6 items per request');
            return false;
        }

        let item = RequestMaterialItem({
            componentId:        '{{$component->id}}',
            componentItemList:  @json($componentItem_options),
            materialList:       @json($material_options)
        });

        item.handler.deleteCallback((dom)=>{
                
            if(count == 1){
                alert('At least one item must remain');
                return false;
            }

            if(confirm('Are you sure you want to delete this item?')){
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
        document.location.href = '/material_quantity_requests';
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
                document.location.href = '/request_material/{{$material_quantity_request->id}}';
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

                if(reply.status <= 0 ){
                    window.util.unblockUI();
                    alert(reply.message);
                    return false;
                };

                window.util.unblockUI();

                document.location.href = '/material_quantity_request/'+reply.data.id;

            });
        }

    @endif
</script>

@endsection