@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/coupons">
                    <span>
                        Coupon
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Display
                    </span>	
                    <i class="ms-2 bi bi-file-earmark-plus"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <x-folder-details title="Coupon" :items="$coupon_details"></x-folder-details>

    <div class="form-container">
        <div class="form-header">
            &nbsp;
        </div>
        <div class="form-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="text" id="amount" class="form-control" disabled="true" value="{{$coupon->amount}}"/>
                    </div>
                </div>                
            </div>

             

            <div class="row mt-5">
                <div class="col-12 text-end">
                 
                   
                    @if($coupon->status == 'PEND')    
                        <button class="btn btn-danger d-none" id="deleteBtn">Delete</button>    
                        <button class="btn btn-primary" id="editBtn">Edit</button>
                        <button class="btn btn-outline-primary" id="reviewLinkBtn">
                            Review Link
                            <i class="bi bi-copy"></i>
                        </button>
                        <button class="btn btn-primary d-none" id="updateBtn">Update</button>
                    @endif

                    @if($coupon->status == 'APRV')
                        <button class="btn btn-danger" id="requestVoidBtn">Request Void</button>
                        <button class="btn btn-primary" id="generateBtn" onclick="window.document.location = '/coupon/generate/{{$coupon->id}}'">Generate</button>
                    @endif

                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const amount    = $q('#amount').first();

    const deleteBtn         = $q('#deleteBtn').first();
    const editBtn           = $q('#editBtn').first();
    const updateBtn         = $q('#updateBtn').first();
    const generateBtn       = $q('#generateBtn').first();
    const requestVoidBtn    = $q('#requestVoidBtn').first();
    const reviewLinkBtn     = $q('#reviewLinkBtn').first();
    
    const cancelBtn  = $q('#cancelBtn').first();
    

    if(reviewLinkBtn){
        reviewLinkBtn.onclick = async ()=>{
            let test = await window.util.copyToClipboard('{{ url("/review/coupon/".$coupon->id); }}');
            if(test){
                alert('Review Link for "Coupon: {{$coupon->id}}" copied!');
            }else{
                alert('Failed to copy');
            }
        }
    }
    
    amount.onkeypress = (e)=>{
        return window.util.inputNumber(amount,e,2,false);
    }


    cancelBtn.onclick = (e) => {
        window.util.navTo('/coupons');
    }

    if(editBtn){

        editBtn.onclick = (e) =>{
            
            editBtn.classList.add('d-none');
            deleteBtn.classList.add('d-none');
            reviewLinkBtn.classList.add('d-none');
            
            updateBtn.classList.remove('d-none');
            deleteBtn.classList.remove('d-none');

            cancelBtn.onclick = (e) => {
                window.util.navReload();
            }

            amount.disabled = false;

        }
    }

    if(updateBtn){

        updateBtn.onclick = (e) =>{
            window.util.blockUI();

            window.util.$post('/api/coupon/update',{
                id      : '{{$coupon->id}}',
                amount  : amount.value,           
            }).then(reply=>{
                
                window.util.unblockUI();

                if(reply.status <= 0 ){
                    window.util.showMsg(reply);
                    return false;
                };
        
                window.util.navReload();
            
            });
        }

        
        amount.onkeyup = (e) => {
            if(!amount.disabled && e.keyCode == 13){
                updateBtn.click();    
            }
        }
    }


    if(deleteBtn){
        deleteBtn.onclick = async (e) =>{

            let check = await window.util.confirm('Are you sure you want to delete this Coupon?');

            if(!check) return false;

            window.util.blockUI();

            window.util.$post('/api/coupon/delete',{
                id      : '{{$coupon->id}}'       
            }).then(reply=>{
                
                window.util.unblockUI();

                if(reply.status <= 0 ){
                    window.util.showMsg(reply);
                    return false;
                };
        
                window.util.navTo('/coupons');
            
            });
        }
    }


    if(requestVoidBtn){
        requestVoidBtn.onclick = async (e) =>{

            let check = await window.util.confirm('Are you sure you want to request voiding of this Coupon?');

            if(!check) return false;

            window.util.blockUI();

            window.util.$post('/api/coupon/request_void',{
                id      : '{{$coupon->id}}'       
            }).then(reply=>{
                
                window.util.unblockUI();

                if(reply.status <= 0 ){
                    window.util.showMsg(reply);
                    return false;
                };
        
                window.util.navReload();
            
            });
        }
    }


    
</script>
</div>
@endsection