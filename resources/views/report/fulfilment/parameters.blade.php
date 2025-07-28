@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/roles">
                        <span>
                        Report
                        </span>                    
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Fulfilment
                        </span>                    
                        <i class="ms-2 bi bi-list-ul"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-3">
            <div class="folder-form-tab">
                Fulfilment Report
            </div>
            <div class="folder-form-body">

                <div class="row mb-3">
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>From</label>
                            <input type="text" class="form-control" id="from" readonly="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="form-group">
                            <label>To</label>
                            <input type="text" class="form-control" id="to" readonly="true"/>
                        </div>
                    </div>
                </div>


                <div class="row mb-3">
                    <div class="col-lg-12 text-end">
                        <button id="print_btn" class="btn btn-warning me-3">Print</button>
                        <button id="submit_btn" class="btn btn-primary m3-3">Generate</button>
                        <button id="cancel_btn" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            </div><!-- div folder-form-body-->
        </div>
    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const from                  = $q('#from').first();
        const to                    = $q('#to').first();
        const submitBtn             = $q('#submit_btn').first();
        const printBtn              = $q('#print_btn').first();

        const date_config = {
            autohide:true,
        };

        const from_dp = new window.util.Datepicker(from, date_config); 

        const to_dp = new window.util.Datepicker(to, date_config);
        
        submitBtn.onclick = (e)=>{
            e.preventDefault();

            let from_val  = from_dp.getDate('yyyy-mm-dd') ?? '';
            let to_val    = to_dp.getDate('yyyy-mm-dd') ?? '';
        
            
            let query = new URLSearchParams({
                from : from_val,
                to   : to_val,
            });

            
            window.open('/report/fulfilment/generate?'+query,'_blank').focus();
        }

        printBtn.onclick = (e)=>{
            e.preventDefault();

            let from_val  = from_dp.getDate('yyyy-mm-dd') ?? '';
            let to_val    = to_dp.getDate('yyyy-mm-dd') ?? '';
        
            
            let query = new URLSearchParams({
                from : from_val,
                to   : to_val,
            });

            
            window.open('/report/fulfilment/print?'+query,'_blank').focus();
        }


    </script>
</div>
@endsection