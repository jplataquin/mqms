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
                <a href="#" class="active">
                    <span>
                       Component
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <table class="record-table-horizontal mb-3"  hx-boost="true" hx-select="#content" hx-target="#main">
        <tbody>
            <tr>
                <th>Project</th>
                <td>
                    <a href="/accomplishment/project/{{$project->id}}">{{$project->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Section</th>
                <td>
                    <a href="/accomplishment/section/{{$section->id}}">{{$section->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Contract Item</th>
                <td>
                    <a href="/accomplishment/contract_item/{{$contract_item->id}}">{{$contract_item->name}}</a>
                </td>
            </tr>
            <tr>
                <th>Component</th>
                <td>
                   {{$component->name}}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="/accomplishment/component/{{$component->id}}/add" class="btn btn-primary" hx-boost="true" hx-select="#content" hx-target="#main">
                <i class="bi bi-plus-lg me-1"></i> Add Registry Entry
            </a>
        </div>
    </div>

    <div class="container px-0" id="list-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 text-secondary fw-bold"><i class="bi bi-list-stars ms-1"></i> Accomplishment Registry Records</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive d-none" id="tableResponsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th scope="col" class="py-3 ps-4">Entry Date</th>
                                <th scope="col" class="py-3">Type</th>
                                <th scope="col" class="py-3 text-end">Quantity</th>
                                <th scope="col" class="py-3">Remarks</th>
                                <th scope="col" class="py-3">Created By</th>
                                <th scope="col" class="py-3 pe-4">Created At</th>
                            </tr>
                        </thead>
                        <tbody id="list">
                            <!-- Populated dynamically via Adarna.js -->
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center py-5 text-muted d-none" id="emptyState">
                    <i class="bi bi-file-earmark-plus display-1 text-secondary opacity-50 mb-3 d-block"></i>
                    <p class="fs-5 mb-0">No accomplishment records found for this component.</p>
                    <small>Click "Add Registry Entry" to create your first record.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <button id="showMoreBtn" class="btn w-100 btn-primary" style="display: none;">Show More</button>
        </div>
    </div>

</div>
</div>

<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

    const list            = $q('#list').first();
    const showMoreBtn     = $q('#showMoreBtn').first();
    const emptyState      = $q('#emptyState').first();
    const tableResponsive = $q('#tableResponsive').first();
    
    let page            = 1;
    let order           = 'DESC';
    let orderBy         = 'entry_data';
    const limit         = 10;
    
    const t = new Template();

    function renderRows(data){
        data.map(item => {
            let displayEntryData = item.entry_data ? item.entry_data.substring(0, 10) : 'N/A';
            let displayCreatedAt = item.created_at ? $util.dateTime(new Date(item.created_at)).full() : 'N/A';
            
            let typeBadge = t.span({class: 'badge bg-success text-white'}, 'ACTUAL');
            if (item.type !== 'ACTUAL') {
                typeBadge = t.span({class: 'badge bg-info text-dark'}, 'TARGET');
            }

            let formattedQty = parseFloat(item.quantity).toFixed(2);
            let unitText = '{{ $component->unit_text }}';

            let row = t.tr({class: 'selectable-div'}, () => {
                t.td({class: 'ps-4 fw-semibold'}, displayEntryData);
                t.td(() => {
                    $el.append(typeBadge).to(t.current());
                });
                t.td({class: 'text-end fw-bold text-primary'}, `${formattedQty} ${unitText}`);
                t.td(() => {
                    t.span({class: 'text-wrap d-inline-block', style: 'max-width: 300px;'}, item.remarks || '');
                });
                t.td(item.creator_name || 'System');
                t.td({class: 'pe-4 text-muted small'}, displayCreatedAt);
            });

            $el.append(row).to(list);
        });
    }

    function showData() {
        window.util.blockUI();

        window.util.$get('/api/accomplishment/record/list', {
            component_id: '{{ $component->id }}',
            page: page,
            limit: limit,
            order_by: orderBy,
            order: order
        }).then(reply => {
            window.util.unblockUI();

            if (reply.status <= 0) {
                window.util.showMsg(reply);
                return false;
            }

            if (page === 1 && reply.data.length === 0) {
                emptyState.classList.remove('d-none');
                tableResponsive.classList.add('d-none');
                showMoreBtn.style.display = 'none';
                return;
            }

            if (page === 1) {
                tableResponsive.classList.remove('d-none');
                emptyState.classList.add('d-none');
            }

            page++;

            if (reply.data.length) {
                renderRows(reply.data);
            }
            
            if (reply.data.length < limit) {
                showMoreBtn.style.display = 'none';
            } else {
                showMoreBtn.style.display = 'block';
            }
        });
    }

    // Initial load
    showData();

    showMoreBtn.onclick = () => {
        showData();
    }
</script>
@endsection
