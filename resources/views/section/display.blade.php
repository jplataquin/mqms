@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<h5>Project » Section » Display</h5>
<hr>

    <div class="row">

        <div class="col-lg-12">
            <table class="w-100 table">
                <tbody>
                    <tr>
                        <th>Project</th>
                        <td>{{$project->name}}</td>
                    </tr>
                    <tr>
                        <th>Section ID</th>
                        <td>
                            {{str_pad($section->id,6,0,STR_PAD_LEFT)}}
                        </td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>
                            <input type="text" id="sectionName" value="{{$section->name}}" disabled="true" class="form-control"/>
                        </td>
                    </tr>
                </tbody>
            </table>    
        </div>

    </div>

    <div class="row mt-5">
        <div class="col-lg-6">
            <button class="btn btn-danger" id="deleteBtn">Delete</button>
          
        </div>
        <div class="col-lg-6 text-end">

            <button class="btn btn-warning" id="printBtn">Print</button>
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
            <button class="btn btn-warning d-none" id="updateBtn">Update</button>
        </div>
    </div>

    <hr>
    
    <div class="mt-3">
        
        <div class="">
            <h3>Contract Items</h3>
        </div>

        <div class="row">
            <div class="col-lg-12 col-sm-12 text-end">
                   <button id="createBtn" class="btn btn-warning">Create</button>
            </div>
        </div>

        <div id="contract_items" class="mt-3">
            @foreach($contract_items as $contract_item)

                <div class="item row selectable-div fade-in border mb-3" data-id="{{$contract_item->id}}">
                    <div class="col-lg-12">
                        <h3>{{$contract_item->item_code}}</h3>
                        <h6> 

                            {{$contract_item->description}}

                            @if(isset($unit_options[ $contract_item->unit_id ]))
                                {{$contract_item->contract_quantity}} {{ $unit_options[ $contract_item->unit_id ]->text }}
                            @endif
                        </h6>
                    </div>
                </div>

            @endforeach
        </div>

    </div>
</div>

<script type="module">
    import {$q,$el, Template} from '/adarna.js';

    let sectionName                 = $q('#sectionName').first();
    let editBtn                     = $q('#editBtn').first();
    let updateBtn                   = $q('#updateBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let deleteBtn                   = $q('#deleteBtn').first();

    let createBtn                   = $q('#createBtn').first();
    let printBtn                    = $q('#printBtn').first();

    const unit_options = @json($unit_options);

    printBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/project/section/print/{{$section->id}}');
    }

    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        sectionName.disabled = false;
     
        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }


    deleteBtn.onclick = ()=>{

        let answer = prompt('Are you sure you want to delete this Section? \n If so please type "{{$section->name}}"');

        if(answer != "{{$section->name}}"){
            window.util.showMsg('Invalid answer');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/section/delete',{
            id: "{{$section->id}}"
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            window.util.navTo('/project/{{$project->id}}');
        });
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/section/update',{
            name        : sectionName.value,
            id          : '{{$section->id}}'
        }).then(reply=>{

            if(reply.status <= 0){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            }

            document.location.reload(true);
        });
    }

    cancelBtn.onclick = (e)=>{
        window.util.navTo('/project/{{$project->id}}');
    }


    createBtn.onclick = ()=>{

        window.util.navTo('/project/section/contract_item/create/{{$section->id}}');
    }

    $q('.item').apply((el)=>{

        el.onclick = (e)=>{
            window.util.navTo('/project/section/contract_item/'+el.getAttribute('data-id'));
        }
    });

</script>
</div>
@endsection