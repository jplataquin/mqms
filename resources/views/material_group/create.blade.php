@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/master_data/material/groups">
                    <span>
                        Material Group
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
                    Create Material Group
                </div>

                <div class="form-body">
                    <div class="row">

                        <div class="col-12">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" id="materialGroup" class="form-control"/>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label>Specification Template <span class="text-muted small">(Required for Material Item creation)</span></label>
                                <textarea id="specificationTemplate" class="form-control" rows="3" placeholder="e.g. [Width:number] mm x [Length:number] mm - [Color:text]"></textarea>
                                <div class="form-text text-muted mt-1 small">
                                    Define placeholder variables enclosed in brackets: <code>[Label:number]</code> for numeric values or <code>[Label:text]</code> (or <code>[Label]</code>) for text. All text outside brackets acts as static formatting.
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

        let createBtn               = $q('#createBtn').first();
        let cancelBtn               = $q('#cancelBtn').first();
        let materialGroup           = $q('#materialGroup').first();
        let specificationTemplate   = $q('#specificationTemplate').first();

        createBtn.onclick = (e) => {

            window.util.blockUI();

            window.util.$post('/api/material/group/create',{
                name: materialGroup.value,
                specification_template: specificationTemplate.value
            }).then(reply=>{

                window.util.unblockUI();
                    
                if(reply.status <= 0 ){
                    
                    window.util.showMsg(reply);
                    return false;
                };

        
                window.util.navTo('/master_data/material/group/'+reply.data.id);

            
            });
        }

        cancelBtn.onclick = (e) => {
            window.util.navTo('/master_data/material/groups');
        }

    </script>
</div>
@endsection
