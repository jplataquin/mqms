@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
<div class="breadcrumbs" hx-boost="true">
        <ul>
            <li>
                <a href="/master_data/suppliers" hx-select="#content" hx-target="#main">
                    <span>
                       Suppliers
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
            Create Supplier
        </div>

        <div class="form-body">
            <div class="row">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>* Supplier Name</label>
                        <input type="text" id="supplierName" class="form-control"/>
                    </div>
                </div>

                <div class="col-lg-12 mb-3">
                    <div class="form-group">
                        <label>* Address</label>
                        <textarea id="address" class="form-control"></textarea>
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-lg-6">
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>* Primary Contact Person</label>
                                <input type="text" id="primaryContactPerson" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>* Primary Contact No.</label>
                                <input type="text" id="primaryContactNo" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>* Primary Email</label>
                                <input type="email" id="primaryEmail" class="form-control"/>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Secondary Contact Person</label>
                                <input type="text" id="secondaryContactPerson" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Secondary Contact No.</label>
                                <input type="text" id="secondaryContactNo" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Secondary Email</label>
                                <input type="text" id="secondary" class="form-control"/>
                            </div>
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

    let createBtn              = $q('#createBtn').first();
    let cancelBtn              = $q('#cancelBtn').first();
    let supplierName           = $q('#supplierName').first();
    let address                = $q('#address').first();

    let primaryContactperson   =  $q('#primaryContactPerson').first();
    let primaryContactNo       =  $q('#primaryContactNo').first();
    let primaryEmail           =  $q('#primaryEmail').first();

    let secondaryContactperson =  $q('#secondaryContactPerson').first();
    let secondaryContactNo     =  $q('#secondaryContactNo').first();
    let secondaryEmail         =  $q('#secondaryEmail').first();
    
    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/supplier/create',{
            name: supplierName.value,
            address: address.value,
            
            primary_contact_person: primaryContactPerson.value,
            primary_contact_no: primaryContactNo.value,
            primary_email: primaryEmail.value,
            
            secondary_contact_person: secondaryContactPerson.value,
            secondary_contact_no: secondaryContactNo.value,
            secondary_email: secondaryEmail.value
        }).then(reply=>{

            window.util.unblockUI();
                
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            window.util.navTo('/master_data/supplier/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/mater_data/suppliers');
    }

</script>
</div>
@endsection