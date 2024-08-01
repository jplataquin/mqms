@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
                    <span>
                        Master Data
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                       Payment Term
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>		
                </a>
            </li>
        </ul>
    </div>
<hr>

    <div class="row">

        <div class="col-lg-12">
            <div class="form-group">
                <label>Text</label>
                <input type="text" id="text" class="form-control"/>
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

<script type="module">
    import {$q} from '/adarna.js';

    let createBtn              = $q('#createBtn').first();
    let cancelBtn              = $q('#cancelBtn').first();
    let text                   = $q('#text').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/payment_term/create',{
            text: text.value
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };

            window.util.navTo('/master_data/payment_term/'+reply.data.id);

        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/master_data/payment_terms');

    }

</script>
</div>
@endsection