@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<h5>Master Data » Unit » Display</h5>
<hr>

    <div class="row">

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Text</label>
                <input type="text" disabled="true" id="text" value="{{$unit->text}}" class="form-control"/>
            </div>
        </div>   

    </div>

    <div class="row mt-5 mb-3">
        <div class="col-6 text-start">
            <button class="btn btn-danger" id="deleteBtn">Delete</button>
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
            <button class="btn btn-warning d-none" id="updateBtn">Update</button>
        </div>
    </div>

   
    
<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    let text                        = $q('#text').first();
    let editBtn                     = $q('#editBtn').first();
    let updateBtn                   = $q('#updateBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let deleteBtn                   = $q('#deleteBtn').first();
    
    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        text.disabled              = false;
     
        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/unit/update',{
            text    : text.value,
            id      : '{{$unit->id}}'
        }).then(reply=>{

            if(reply.status <= 0){
                window.util.unblockUI();
                window.util.showMsg(reply.message);
                return false;
            }

            document.location.reload(true);
        });
    }


    cancelBtn.onclick = (e)=>{
        document.location.href = '/master_data/units';
    }

    

    deleteBtn.onclick = (e)=>{

        let answer = confirm('Are you sure you want to delete this Component Unit?');

        if(!answer){
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/unit/delete',{
            id: "{{$unit->id}}"
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            window.location.href = '/master_data/units';
        });
    }
    


</script>
</div>
@endsection