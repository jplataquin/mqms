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
                 
                    
                    <button class="btn btn-danger" id="rejectBtn">Reject</button>
                   
                    <button class="btn btn-primary" id="approveBtn">Approve</button>
                   
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const amount      = $q('#amount').first();
    const cancelBtn   = $q('#cancelBtn').first();
    const approveBtn  = $q('#approveBtn').first();
    const rejectBtn   = $q('#rejectBtn').first();
    

    approveBtn.onclick = async (e) => {
        
        let check = await window.util.confirm('Are you sure you want to APPROVE this Coupon?');

        if(!check){
            return false;
        }

        window.util.blockUI();

        window.util.$get('/api/review/coupon/approve',{
            id     : '{{$coupon->id}}',
            amount : amount.value
        }).then(reply=>{

            window.util.unblockUI();
                

            if(reply.status <= 0){
                
                window.util.showMsg(reply);
                return false;
            };

            window.util.navReload();
            
        });
    } 

    rejectBtn.onclick = async (e) => {
        
        let check = await window.util.confirm('Are you sure you want to REJECT this Coupon?');

        if(!check){
            return false;
        }

        window.util.blockUI();

        window.util.$get('/api/review/coupon/reject',{
            id     : '{{$coupon->id}}',
            amount : amount.value
        }).then(reply=>{

            window.util.unblockUI();
                

            if(reply.status <= 0){
                
                window.util.showMsg(reply);
                return false;
            };

            
            window.util.navReload();
            
        });
    } 

    cancelBtn.onclick = (e) => {
        window.util.navTo('/review/coupons');
    }


    
</script>
</div>
@endsection