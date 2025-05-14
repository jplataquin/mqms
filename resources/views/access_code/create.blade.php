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
                        <label>Subject</label>
                        <input type="text" id="subject" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 mb-3">
                    <div class="form-group">
                        <label>Scope</label>
                        <select class="form-select" id="scope" >
                            <option value="own">Own</option>
                            <option value="all">All</option>
                        </select>
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
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input actions" type="checkbox" value="{{$val}}">
                                        <label class="form-check-label">
                                            {{ str_replace('_',' ',$val) }}
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control action_description" id="{{$val}}_description"></textarea>
                                    </div>
                                </div>
                            @endforeach
                            
                            Other: <input type="text" class="form-control" id="action_other"/>
                        </div>
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

    const subject             = $q('#subject').first();
    const scope               = $q('#scope').first();
    const actions             = $q('.actions').items();
    const action_other        = $q('#action_other').first();
    const description         = $q('#description').first();

    const createBtn           = $q('#createBtn').first();
    const cancelBtn           = $q('#cancelBtn').first();
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

        let action_list = [];

        actions.map(item=>{

            if(item.checked){
                action_list.push(item.value);
            }
        });

        if(action_other.value){
            action_list.push(action_other.value);
        }

        window.util.$post('/api/access_code/create',{
            subject: subject.value,
            scope: scope.value,
            actions: action_list.concat(),
            description: description.value
        }).then(reply=>{
            
            window.util.unblockUI();
                
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };


            window.util.navTo('/access_codes');


        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/access_codes';

    }

</script>
</div>
@endsection