@extends('layouts.app')

@section('content')
<div id="content">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/purchase_orders">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Bulk PO
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <h1>Target Status: {{$target_status_text}}</h1>

    <div class="mt-5">
        <form id="form">
            @foreach($pos as $project_id => $po_list)
                <div>
                    <h3>{{$project_arr[$project_id]->name}}</h3>

                    <div class="d-flex flex-wrap justify-content-evenly">
                        @foreach($po_list as $item)
                            <input type="hidden" value="{{$item['po']->id}}" name="po[]"/>
                            @php
                                $orig_status = $original_statuses[$item['po']->id] ?? ($item['po']->status == 'VOID' ? 'REVO' : 'PEND');
                            @endphp
                            <input type="hidden" class="revert-status-input" data-id="{{$item['po']->id}}" value="{{$orig_status}}" name="original_status[{{$item['po']->id}}]"/>
                            <div class="border border-secondary p-2">
                                <div>
                                    @if($item['flag'])
                                        <span class="text-success">[✔]</span>
                                    @else
                                        <span class="text-danger">[✖]</span>
                                    @endif

                                    {{ str_pad($item['po']->id, 6, 0, STR_PAD_LEFT) }}
                                </div>

                                <div>
                                    P {{ number_format($item['total'],2) }}
                                </div>
                                <div>
                                    {{ $item['created_at'] }}
                                </div>

                                <div>
                                    @if($item['po']->status == 'APRV')
                                        PEND > <span class="text-success">APRV</span>
                                    @endif

                                    @if($item['po']->status == 'REJC')
                                        PEND > <span class="text-danger">REJC</span>
                                    @endif

                                    @if($item['po']->status == 'VOID')
                                        REVO > <span class="text-primary">VOID</span>
                                    @endif
                                </div>
                            </div>

                        @endforeach
                    </div>
                </div>
            @endforeach
        </form>



          <div class="row mt-5">
            <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">
                <button class="btn btn-warning" id="revertBtn">Revert</button>
                <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const revertBtn = $q('#revertBtn').first();
    const cancelBtn = $q('#cancelBtn').first();

    if(cancelBtn){
        cancelBtn.onclick = (e) => {
            e.preventDefault();
            window.location.href = '/review/bulk/purchase_orders';
        };
    }

    if(revertBtn){
        revertBtn.onclick = async (e) => {
            e.preventDefault();

            let ans = await window.util.confirm('Are you sure you want to revert these records? This can only be done once.');

            if(ans){
                window.util.blockUI();

                const data = {};
                const inputs = document.querySelectorAll('.revert-status-input');
                inputs.forEach(input => {
                    if (input.dataset.id && input.value) {
                        data[input.dataset.id] = input.value;
                    }
                });

                if (Object.keys(data).length === 0) {
                    window.util.unblockUI();
                    window.util.alert('No records available to revert.', 'error');
                    return;
                }

                revertBtn.disabled = true;

                window.util.$post('/api/review/bulk/purchase_order/revert', {
                    data: JSON.stringify(data)
                }).then(reply => {
                    window.util.unblockUI();

                    if(reply.status == 1){
                        window.util.alert('Records have been reverted successfully', 'success').then(() => {
                            window.location.href = '/review/bulk/purchase_orders';
                        });
                    } else {
                        revertBtn.disabled = false;
                        window.util.alert(reply.message || 'An error occurred', 'error');
                    }
                }).catch(err => {
                    revertBtn.disabled = false;
                    window.util.unblockUI();
                    window.util.alert('An unexpected error occurred', 'error');
                });
            }
        };
    }
</script>
@endsection