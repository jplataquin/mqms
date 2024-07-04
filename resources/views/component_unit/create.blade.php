@extends('layouts.app')

@section('content')
<div class="container">
<h5>Master Data » Component Unit » Create</h5>
<hr>

    <div class="row">

        <div class="col-lg-12">
            <div class="form-group">
                <label>Text</label>
                <input type="text" id="text" class="form-control"/>
            </div>
        </div>


    </div>

    <div class="row mt-5">
        <div class="col-12 text-end">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="createBtn">Create</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    let createBtn              = $q('#createBtn').first();
    let cancelBtn              = $q('#cancelBtn').first();
    let text                   = $q('#text').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/component_unit/create',{
            text: text.value
        }).then(reply=>{

            if(reply.status <= 0 ){
                window.util.unblockUI();
                window.util.showMsg(reply.message);
                return false;
            };

            window.util.unblockUI();
      
            document.location.href = '/master_data/component_unit/'+reply.data.id;

        
        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/master_data/component_units';

    }

</script>

@endsection