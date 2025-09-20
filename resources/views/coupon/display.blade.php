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
            Display Coupon
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
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                   
                    @if($coupon->status == 'PEND')    
                        <button class="btn btn-danger" id="deleteBtn">Delete</button>    
                        <button class="btn btn-primary" id="editBtn">Edit</button>
                        <button class="btn btn-primary d-none" id="updateBtn">Update</button>
                    @endif

                    @if($coupon->status == 'APRV')    
                        <button class="btn btn-primary" id="printBtn">Print</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const amount    = $q('#amount').first();

    const deleteBtn  = $q('#deleteBtn').first();
    const editBtn    = $q('#editBtn').first();
    const updateBtn  = $q('#editBtn').first();
    const printBtn   = $q('#printBtn').first();
    
    const cancelBtn  = $q('#cancelBtn').first();
    
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
            updateBtn.classList.remove('d-none');

            cancelBtn.onclick = (e) => {
                window.util.navTo('/coupon/{{$coupon->id}}');
            }

            amount.disabled = false;
        }
    }

    if(updateBtn){
        updateBtn.onclick = (e) =>{
            
        }
    }
    
</script>
</div>
@endsection