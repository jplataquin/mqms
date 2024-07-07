@extends('layouts.app')

@section('content')
<div class="container">
<h5>Request Material » Create</h5>
<hr>
    <table class="table">
        <tbody>
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
                <th>Description</th>
                <td>
                    <textarea class="w-100" id="description"></textarea>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div id="itemContainer"></div>
    <div>
        <button class="btn btn-warning w-100 mt-3" id="addBtn">Add More</button>
    </div>
    <div class="row mt-5">
        <div class="col-12 text-end">
        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
        <button class="btn btn-primary" id="createBtn">Create</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,$el} from '/adarna.js';
    import RequestMaterialItem from '/ui_components/RequestMaterialItem.js';

    let itemContainer = $q('#itemContainer').first();
    let addBtn        = $q('#addBtn').first();
    let createBtn     = $q('#createBtn').first();
    let cancelBtn     = $q('#cancelBtn').first();
    let description   = $q('#description').first();

    let count = 1;

    let itemForm = RequestMaterialItem({
        componentId:        '{{$component->id}}',
        componentItemList:  @json($componentItem_options),
        materialList:       @json($material_options),
        unitOptions:        @json($unit_options)
    });

    itemForm.handler.deleteCallback((dom)=>{
        
        if(count == 1){
            alert('At least one item must remain');
            return false;
        }

        if(confirm('Are you sure you want to delete this item?')){
            $el.remove(dom);
        }

        count--;

    });

    $el.append(itemForm).to(itemContainer);

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

    function setIndexNumber(){
        
        let i = 1;

        $q('.items').apply(item=>{

            item.handler.setIndexNumber(i);
            i++;
        });
    }

    function formValidation(){

        return true;
    }

    createBtn.onclick = (e)=>{
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

        window.util.$post('/api/material_quantity_request/create',{
            project_id:'{{$project->id}}',
            section_id: '{{$section->id}}',
            component_id: '{{$component->id}}',
            description: description.value,
            items:JSON.stringify(items)
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

    cancelBtn.onclick = ()=>{
        document.location.href = '/material_quantity_requests';
    }
    
    setIndexNumber();
</script>

@endsection