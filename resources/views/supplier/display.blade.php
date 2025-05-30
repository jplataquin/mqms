@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/master_data/suppliers">
                    <span>
                       Suppliers
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Display
                    </span>
                    <i class="ms-2 bi bi-display"></i>		
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            Supplier
        </div>
        <div class="form-body">
            <div class="row mb-3">

                <div class="col-lg-12 mb-3">
                    <div class="form-group">
                        <label>Supplier Name</label>
                        <input type="text" id="supplierName" value="{{$supplier->name}}" disabled="true" class="form-control"/>
                    </div>
                </div>

                <div class="col-lg-12 mb-3">
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="address" disabled="true" class="form-control">{{$supplier->address}}</textarea>
                    </div>
                </div>

            </div>

            <div class="row mb-3">

                <div class="col-lg-4 mb-3">
                    <div class="form-group">
                        <label>Primary Contact Person</label>
                        <input type="text" id="primaryContactPerson" value="{{$supplier->primary_contact_person}}" disabled="true" class="form-control"/>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="form-group">
                        <label>Primary Contact No.</label>
                        <input type="text" id="primaryContactNo" value="{{$supplier->primary_contact_no}}" disabled="true" class="form-control"/>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="form-group">
                        <label>Primary Email</label>
                        <input type="email" id="primaryEmail" value="{{$supplier->primary_email}}" disabled="true" class="form-control"/>
                    </div>
                </div>
            </div>
                

            <div class="row mb-3">
                <div class="col-lg-4 mb-3">
                    <div class="form-group">
                        <label>Secondary Contact Person</label>
                        <input type="text" id="secondaryContactPerson" value="{{$supplier->secondary_contact_person}}" disabled="true" class="form-control"/>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="form-group">
                        <label>Secondary Contact No.</label>
                        <input type="text" id="secondaryContactNo" value="{{$supplier->secondary_contact_no}}" disabled="true" class="form-control"/>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="form-group">
                        <label>Secondary Email</label>
                        <input type="text" id="secondaryEmail" value="{{ $supplier->secondary_email }}" disabled="true" class="form-control"/>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6 text-start">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                </div>
            </div>

        </div>
    </div>
</div>

    

    

    

<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    
    let supplierName           = $q('#supplierName').first();
    let address                = $q('#address').first();

    let primaryContactperson   =  $q('#primaryContactPerson').first();
    let primaryContactNo       =  $q('#primaryContactNo').first();
    let primaryEmail           =  $q('#primaryEmail').first();

    let secondaryContactperson =  $q('#secondaryContactPerson').first();
    let secondaryContactNo     =  $q('#secondaryContactNo').first();
    let secondaryEmail         =  $q('#secondaryEmail').first();
    
    let searchBtn                   = $q('#searchBtn').first();
    let editBtn                     = $q('#editBtn').first();
    let updateBtn                   = $q('#updateBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let deleteBtn                   = $q('#deleteBtn').first();
    
    editBtn.onclick = (e)=>{
        e.preventDefault();

        $q('.form-control').apply((el)=>{
            el.disabled = false;
        });
        
        editBtn.classList.add('d-none');
        updateBtn.classList.remove('d-none');

        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/supplier/update',{
            name: supplierName.value,
            address: address.value,
            
            primary_contact_person: primaryContactPerson.value,
            primary_contact_no: primaryContactNo.value,
            primary_email: primaryEmail.value,
            
            secondary_contact_person: secondaryContactPerson.value,
            secondary_contact_no: secondaryContactNo.value,
            secondary_email: secondaryEmail.value,

            id: '{{$supplier->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                
                window.util.showMsg(reply);
                return false;
            }

            window.util.navReload();
        });
    }


    cancelBtn.onclick = (e)=>{
        window.util.navTo('/suppliers');
    }

    // createBtn.onclick = (e)=>{
    //     window.util.navTo('/supplier/create');
    // }

    deleteBtn.onclick = (e)=>{

        let answer = prompt('Are you sure you want to delete this Project? \n If so please type "{{$supplier->name}}"');

        if(answer != "{{$supplier->name}}"){
            window.util.showMsg('Invalid answer');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/supplier/delete',{
            id: "{{$supplier->id}}"
        }).then(reply=>{

            window.util.unblockUI();
            
            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navTo('/suppliers');
        });
    }
    


    

</script>
</div>
@endsection