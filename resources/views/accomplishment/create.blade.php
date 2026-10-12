@extends('layouts.app')

@section('content')
<div id="content">
    
    <div class="container">

        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/accomplishment">
                        <span>
                        Accomplishment
                        </span>
                    </a>
                </li>
                <li>
                    <a href="/accomplishment/contract_item/{{$contract_item->id}}">
                        <span>
                        {{$contract_item->item_code}}
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

        <table class="record-table-horizontal mb-3">
            <tbody>
                <tr>
                    <th>Project</th>
                    <td>{{$project->name}}</td>
                </tr>
                <tr>
                    <th>Section</th>
                    <td>{{$section->name}}</td>
                </tr>
                <tr>
                    <th>Contract Item</th>
                    <td><strong>[{{$contract_item->item_code}}]</strong> {{$contract_item->description}}</td>
                </tr>
                <tr>
                    <th>Total Quantity</th>
                    <td>{{number_format($contract_item->contract_quantity, 2)}} {{$contract_item->contract_unit_text}}</td>
                </tr>
            </tbody>
        </table>

        <div class="form-container">
            <div class="form-header">
                Create Accomplishment
            </div>
            <div class="form-body">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" id="type">
                                <option value="ACTUAL" {{ request()->get('type') == 'ACTUAL' ? 'selected' : '' }}>ACTUAL</option>
                                <option value="TARGET" {{ request()->get('type') == 'TARGET' ? 'selected' : '' }}>TARGET</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Entry Date</label>
                            <input type="date" id="entry_date" class="form-control" value="{{date('Y-m-d')}}"/>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" id="quantity" class="form-control"/>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea id="remarks" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            
                <div class="row">
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

        const createBtn   = $q('#createBtn').first();
        const cancelBtn   = $q('#cancelBtn').first();
        
        const type        = $q('#type').first();
        const entry_date  = $q('#entry_date').first();
        const quantity    = $q('#quantity').first();
        const remarks     = $q('#remarks').first();

        quantity.onkeypress = (e) => {
            return window.util.inputNumber(quantity, e, 4, false);
        }

        createBtn.onclick = (e) => {

            window.util.blockUI();

            window.util.$post('/api/accomplishment/create', {
                contract_item_id : '{{$contract_item->id}}',
                type             : type.value,
                entry_date       : entry_date.value,
                quantity         : quantity.value,
                remarks          : remarks.value
            }).then(reply => {
                
                window.util.unblockUI();

                if (reply.status <= 0) {
                    window.util.showMsg(reply);
                    return false;
                }
        
                const isStudio = new URLSearchParams(window.location.search).has('studio') ? '&studio=1' : '';
                window.util.navTo('/accomplishment/contract_item/{{$contract_item->id}}?type=' + type.value + isStudio);
            });
        }

        cancelBtn.onclick = (e) => {
            const isStudio = new URLSearchParams(window.location.search).has('studio') ? '&studio=1' : '';
            window.util.navTo('/accomplishment/contract_item/{{$contract_item->id}}?type=' + type.value + isStudio);
        }
    </script>
</div>
@endsection
