@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">

    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/access_codes">
                    <span>
                        Access Code
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
    <div class="form-container">
        <div class="form-header">
            Create Access Code
        </div>
        <div class="form-body">
            
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <div class="form-group">
                        <label>Scope</label>
                        <select class=""></select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <div class="form-group">
                        <label>Actions</label>

                        <div class="ps-5">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="check-all-actions-btn">
                                <label class="form-check-label">
                                    All
                                </label>
                            </div>
                            @foreach([
                                'add',
                                'view',
                                'update',
                                'delete',
                                'request_void',
                                'revert_to_pending'
                            ] as $val)
                                <div class="form-check">
                                    <input class="form-check-input actions" type="checkbox" value="{{$val}}">
                                    <label class="form-check-label">
                                        {{ str_replace('_',' ',$val) }}
                                    </label>
                                </div>
                            @endforeach
                            
                            Other: <input type="text" class="form-control" class="actions"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-12 mb-3">
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" maxlength="255" id="access_code" class="form-control"/>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="description" class="form-control"></textarea>
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
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    const createBtn           = $q('#createBtn').first();
    const cancelBtn           = $q('#cancelBtn').first();
    const access_code         = $q('#access_code').first();
    const description         = $q('#description').first();
    const checkAllActionsBtn  = $q('#check-all-actions-btn').first();
    
    checkAllActionsBtn.onchange = (e)=>{

        $q('.actions').apply((elem)=>{

            if(checkAllActionsBtn.checked){
                elem.checked = true;
            }else{
                elem.checked = false;
            }
        });
    }

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/access_code/create',{
            code: access_code.value,
            description: description.value
        }).then(reply=>{
            
            window.util.unblockUI();
                
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };


            window.util.navTo('/access_code/'+reply.data.id);


        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/access_codes';

    }

</script>
</div>
@endsection