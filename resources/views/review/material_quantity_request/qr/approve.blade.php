@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="text-center">
            <h1 class="text-success" id="status">Approved</h1>
            <h1>Material Request: {{ str_pad($id,6,0,STR_PAD_LEFT) }}</h1>
            <h2>{{$hash_code}}</h2>

            <div class="mt-5">
                <button id="revertBtn" class="btn btn-warning">Revert Record</button>
            </div>
        </div>
    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const revert_btn = $q('#revertBtn').first();
        const status     = $q('#status').first();

        revert_btn.onclick = async (e)=>{
            e.preventDefault();

            let ans = await window.util.confirm('Are you sure you want to revert this record?');

            if(ans){
                window.util.blockUI();

                window.util.$post('/material_quantity_request/revert_to_pending').then(reply=>{
                    window.util.unblockUI();

                    if(replyl.status <= 0){
                        window.util.showMsg(reply);
                        return false;
                    }

                    revert_btn.style.display = 'none';
                    status.innerHTML = 'Pending';
                    status.classList.remove('text-success');
                    status.classList.add('text-warning');

                });
                
            }
        }
    </script>
</div>
@endsection