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
                        Claim
                    </span>	
                    <i class="ms-2 bi bi-file-earmark-plus"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <x-folder-details title="Details" :items="$coupon_details"></x-folder-details>

    @if($flag == 'valid')
    <div class="form-container">
        <div class="form-header">
            Claim Coupon
        </div>
        <div class="form-body">
            <div class="row">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="text" id="amount" value="{{number_format($coupon->amount,2)}}" class="form-control" disabled="true"/>
                    </div>
                </div>

                
            </div>
            <div class="row mt-3">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="name" class="form-control"/>
                    </div>
                </div>

                
            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="claimBtn">Claim</button>
                </div>
            </div>
        </div>
    </div>
    @endif


    @if($flag == 'invalid')
        <div class="text-center container">
            <h1 class="text-danger">Invalid Coupon</h1>
           
            <br>
            

            <table class="table w-100 mt-3">
                <tr>
                    <th>Code</th>
                    <td>
                        {{$coupon->code}}
                    </td>
                </tr>

                <tr>
                    <th>Correct Code</th>
                    <td>
                         {{$correct_code}} 
                    </td>
                </tr>

                <tr>
                    <th>Amount</th>
                    <td>
                        {{$coupon->amount}}
                    </td>
                </tr>

            </table>
        </div>
    @endif


    @if($flag == 'claimed')
        <div class="text-center container">
            <h1 class="text-warning">This Coupon is already claimed</h1>
        </div>
    
    @endif

    
</div>

@if($flag == 'valid')
<script type="module">
    import {$q} from '/adarna.js';


    const name      = $q('#name').first();
    const createBtn = $q('#createBtn').first();
    const cancelBtn = $q('#cancelBtn').first();
    
    claimBtn.onclick = async (e)=>{

        let check = await window.util.confirm('Are you sure you want to claim this coupon amount P {{ number_format($coupon->amount,2) }}?');

        if(!check){
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/coupon/claim',{
            id      : '{{$coupon->id}}',
            name    : name.value           
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
        window.util.navTo('/home');
    }
    
</script>
@endif
</div>
@endsection