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
                    <a href="/accomplishment/component/{{$component->id}}">
                        <span>
                        Component
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Add Registry
                        </span>
                        <i class="ms-2 bi bi-file-earmark-plus"></i>
                    </a>
                </li>
            </ul>
        </div>

        <hr>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 text-secondary fw-bold"><i class="bi bi-info-circle ms-1"></i> Component Reference Details</h5>
            </div>
            <div class="card-body p-0">
                <table class="record-table-horizontal mb-0 w-100">
                    <tbody>
                        <tr>
                            <th style="width: 200px;">Project</th>
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
                            <th>Component Name</th>
                            <td>{{$component->name}}</td>
                        </tr>
                        <tr>
                            <th>Total Quantity</th>
                            <td>{{$component->quantity}} {{$component->unit_text}}</td>
                        </tr>
                        <tr>
                            <th>Latest Quantity</th>
                            <td>
                               @if($latestActual)
                                   @php
                                       $percentage = $component->quantity > 0 ? ($latestActual->quantity / $component->quantity) * 100 : 0;
                                       $formattedDate = $latestActual->entry_data ? $latestActual->entry_data->format('Y-m-d') : 'N/A';
                                   @endphp
                                   {{ number_format($latestActual->quantity, 2) }} {{$component->unit_text}} ({{ number_format($percentage, 0) }}%) as of {{ $formattedDate }}.
                               @else
                                   -
                               @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($component->status == 'APRV')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{$component->status}}</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-container">
            <div class="form-header">
                Add Accomplishment Registry Entry
            </div>
            <div class="form-body">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Entry Date <span class="text-danger">*</span></label>
                            <input type="date" id="entry_data" class="form-control" value="{{date('Y-m-d')}}" required/>
                            <div class="invalid-feedback d-none" id="entry_data_feedback">Entry date is required.</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="text" id="quantity" class="form-control" placeholder="0.00" required/>
                            <div class="invalid-feedback d-none" id="quantity_feedback">Quantity is required and must be numeric.</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Remarks <span class="text-danger">*</span></label>
                            <textarea id="remarks" class="form-control" rows="4" placeholder="Enter details or remarks..." required></textarea>
                            <div class="invalid-feedback d-none" id="remarks_feedback">Remarks are required.</div>
                        </div>
                    </div>
                </div>
            
                <div class="row">
                    <div class="col-12 text-end">
                        <button class="btn btn-secondary me-2" id="cancelBtn">Cancel</button>
                        <button class="btn btn-primary" id="createBtn">Save Entry</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const createBtn       = $q('#createBtn').first();
        const cancelBtn       = $q('#cancelBtn').first();
        
        const entry_data      = $q('#entry_data').first();
        const quantity        = $q('#quantity').first();
        const remarks         = $q('#remarks').first();

        const entry_data_fb   = $q('#entry_data_feedback').first();
        const quantity_fb     = $q('#quantity_feedback').first();
        const remarks_fb      = $q('#remarks_feedback').first();

        quantity.onkeypress = (e) => {
            return window.util.inputNumber(quantity, e, 4, false);
        }

        createBtn.onclick = (e) => {
            let hasError = false;

            // Reset feedbacks
            entry_data.classList.remove('is-invalid');
            quantity.classList.remove('is-invalid');
            remarks.classList.remove('is-invalid');
            
            entry_data_fb.classList.add('d-none');
            quantity_fb.classList.add('d-none');
            remarks_fb.classList.add('d-none');

            if (!entry_data.value) {
                entry_data.classList.add('is-invalid');
                entry_data_fb.classList.remove('d-none');
                hasError = true;
            }

            if (!quantity.value || isNaN(quantity.value)) {
                quantity.classList.add('is-invalid');
                quantity_fb.classList.remove('d-none');
                hasError = true;
            }

            if (!remarks.value || remarks.value.trim() === '') {
                remarks.classList.add('is-invalid');
                remarks_fb.classList.remove('d-none');
                hasError = true;
            }

            if (hasError) {
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/accomplishment/add', {
                component_id : '{{$component->id}}',
                entry_data   : entry_data.value,
                quantity     : quantity.value,
                remarks      : remarks.value
            }).then(reply => {
                
                window.util.unblockUI();

                if (reply.status <= 0) {
                    window.util.showMsg(reply);
                    return false;
                }
        
                window.util.navTo('/accomplishment/component/{{$component->id}}');
            });
        }

        cancelBtn.onclick = (e) => {
            window.util.navTo('/accomplishment/component/{{$component->id}}');
        }

    </script>
</div>
@endsection
