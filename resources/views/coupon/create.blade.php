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
            Fuel Coupon
        </div>
        <div class="form-body">
            <div class="row">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Amount (PHP)</label>
                        <input type="text" id="amount" class="form-control"/>
                    </div>
                </div>
                
            </div>

            <div class="row">
                
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Quantity (Ltrs)</label>
                        <input type="text" id="quantity" class="form-control"/>
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

    const amount    = $q('#amount').first();
    const createBtn = $q('#createBtn').first();
    const cancelBtn = $q('#cancelBtn').first();
    const quantity  = $q('#quantity').first();
    
    amount.onkeypress = (e)=>{
        return window.util.inputNumber(amount,e,2,false);
    }

    quantity.onkeypress = (e)=>{
        return window.util.inputNumber(amount,e,2,false);
    }

    amount.onkeyup = (e)=>{
        quantity.value = '';
        if(e.keyCode == 13){
            createBtn.click();
        }
    }

    quantity.onkeyup = (e)=>{
     
        amount.value = '';
     
        if(e.keyCode == 13){
            createBtn.click();
        }
    }

    createBtn.onclick = (e)=>{
        window.util.blockUI();

        window.util.$post('/api/coupon/create',{
            amount : amount.value,
            quantity: quantity.value        
        }).then(reply=>{
            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };
    
            window.util.navTo('/coupon/'+reply.data.id);
        
        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/coupons');
    }
    
</script>
</div>
@endsection