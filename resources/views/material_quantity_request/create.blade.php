@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/material_quantity_requests">
                    <span>
                       Material Quantity Requests
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>	
                    <i class="ms-2 bi bi-file-earmark-plus"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>
    <div class="folder-form-container mb-5">
        <div class="folder-form-tab">
            Material Quantity Request
        </div>
        <div class="folder-form-body">
            <div class="row mb-3">
                <div class="col-4">
                    <div class="form-group">
                        <label>Project</label>
                        <input type="text" value="{{$project->name}}" class="form-control" disabled="true"/>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" value="{{$section->name}}" class="form-control" disabled="true"/>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-group">
                        <label>Contract Item</label>
                        <input type="text" value="{{$contract_item->item_code}} - {{$contract_item->description}}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <div class="form-group">
                        <label>Component</label>
                        <input type="text" value="{{$component->name}}" class="form-control" disabled="true"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
        componentItemList:  @json($component_item_options),
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
            componentItemList:  @json($component_item_options),
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
            project_id          : '{{$project->id}}',
            section_id          : '{{$section->id}}',
            contract_item_id    : '{{$contract_item->id}}',
            component_id        : '{{$component->id}}',
            description: description.value,
            items:JSON.stringify(items)
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

           window.util.navTo('/material_quantity_request/'+reply.data.id);

        });
    }

    cancelBtn.onclick = ()=>{
        window.util.navTo('/material_quantity_requests');
    }
    
    setIndexNumber();
</script>
</div>
@endsection