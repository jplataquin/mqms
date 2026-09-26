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
            <tr>
                <th>Total Quantity</th>
                <td>
                   {{ number_format($component->quantity) }} {{$component->unit_text}}
                </td>
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
        </tbody>
    </table>

    @php
        $activeType = strtoupper(request()->get('type', 'ACTUAL'));
        if (!in_array($activeType, ['ACTUAL', 'TARGET'])) {
            $activeType = 'ACTUAL';
        }
    @endphp

    <div class="row mb-3">
        <div class="col-12 text-end">
            <button id="addRegistryBtn" class="btn {{ $activeType == 'TARGET' ? 'btn-success' : 'btn-primary' }}">
                <i class="bi bi-plus-lg me-1"></i> Add Registry Entry
            </button>
        </div>
    </div>

    <div class="folder-form-container mb-3 d-flex" style="gap: 5px;">
        <div id="tabActual" class="folder-form-tab c-pointer" style="background-color: {{ $activeType == 'ACTUAL' ? '#0d6efd' : '#2c3034' }}; color: {{ $activeType == 'ACTUAL' ? 'white' : '#0d6efd' }}; min-width: 150px; transition: all 0.2s;">
            Actual
        </div>
        <div id="tabTarget" class="folder-form-tab c-pointer" style="background-color: {{ $activeType == 'TARGET' ? '#198754' : '#2c3034' }}; color: {{ $activeType == 'TARGET' ? 'white' : '#198754' }}; min-width: 150px; transition: all 0.2s;">
            Target
        </div>
    </div>

    <div class="container px-0" id="list">
        <!-- Populated dynamically via Adarna.js -->
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
    import CreateAccomplishmentForm from '/ui_components/create_forms/CreateAccomplishmentForm.js';

    const list            = $q('#list').first();
    const showMoreBtn     = $q('#showMoreBtn').first();
    const addRegistryBtn  = $q('#addRegistryBtn').first();
    const tabActual       = $q('#tabActual').first();
    const tabTarget       = $q('#tabTarget').first();
    
    let page            = 1;
    let order           = 'DESC';
    let orderBy         = 'entry_data';
    const limit         = 10;
    const urlParams     = new URLSearchParams(window.location.search);
    let currentType     = (urlParams.get('type') || '{{ $activeType }}').toUpperCase();
    if (currentType !== 'TARGET' && currentType !== 'ACTUAL') {
        currentType = 'ACTUAL';
    }
    
    const t = new Template();

    function renderRows(data){
        data.map(item => {
            let displayEntryData = item.entry_data ? item.entry_data.substring(0, 10) : 'N/A';
            let displayCreatedAt = item.created_at ? $util.dateTime(new Date(item.created_at)).full() : 'N/A';
            let formattedQty = parseFloat(item.quantity).toFixed(2) + ' {{ $component->unit_text }}';

            let qtyClass = item.type === 'TARGET' ? 'fw-bold text-success' : 'fw-bold text-primary';

            let row = t.div({class: 'item-container fade-in'}, () => {
                t.div({class: 'item-header'}, `Entry Date: ${displayEntryData} [${item.type}]`);
                t.div({class: 'item-body'}, () => {
                    t.div({class: 'row'}, () => {
                        t.div({class: 'col-12'}, () => {
                            t.span({class: qtyClass}, `Quantity: ${formattedQty}`);
                        });
                    });
                    
                    t.div({class: 'row mt-2'}, () => {
                        t.div({class: 'col-12 small text-muted'}, `Created by ${item.creator_name || 'System'} on ${displayCreatedAt}`);
                    });
                });
            });

            row.onclick = () => {
                window.util.navTo('/accomplishment/record/' + item.id);
            };

            $el.append(row).to(list);
        });
    }

    function showData() {
        window.util.blockUI();

        window.util.$get('/api/accomplishment/record/list', {
            component_id: '{{ $component->id }}',
            type: currentType,
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
                let emptyDiv = t.div({class: 'text-center py-5 text-muted border border-secondary rounded shadow-sm mb-3'}, () => {
                    t.i({class: 'bi bi-file-earmark-plus display-4 text-secondary opacity-50 mb-3 d-block'});
                    t.p({class: 'fs-5 mb-1 fw-bold text-light'}, 'No accomplishment records found');
                    t.small({class: 'text-muted'}, 'Click "Add Registry Entry" to create your first record.');
                });
                $el.append(emptyDiv).to(list);
                showMoreBtn.style.display = 'none';
                return;
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

    function updateTabUI() {
        if (currentType === 'TARGET') {
            tabActual.style.backgroundColor = '#2c3034';
            tabActual.style.color = '#0d6efd';
            tabTarget.style.backgroundColor = '#198754';
            tabTarget.style.color = 'white';
            addRegistryBtn.className = 'btn btn-success';
        } else {
            tabActual.style.backgroundColor = '#0d6efd';
            tabActual.style.color = 'white';
            tabTarget.style.backgroundColor = '#2c3034';
            tabTarget.style.color = '#198754';
            addRegistryBtn.className = 'btn btn-primary';
        }
    }

    function switchTab(type) {
        if (currentType === type) return;
        currentType = type;

        const url = new URL(window.location.href);
        url.searchParams.set('type', currentType);
        window.history.replaceState({}, '', url.toString());

        updateTabUI();

        $el.clear(list);
        page = 1;
        showData();
    }

    // Initial load
    updateTabUI();
    showData();

    showMoreBtn.onclick = () => {
        showData();
    }

    addRegistryBtn.onclick = () => {
        window.util.drawerModal.content('Add Accomplishment Registry Entry', CreateAccomplishmentForm({
            component_id: '{{$component->id}}',
            type: currentType,
            successCallback: () => {
                if (window.parent && typeof window.parent.refreshGanttChart === 'function') {
                    window.parent.refreshGanttChart();
                }
                const url = new URL(window.location.href);
                url.searchParams.set('type', currentType);
                window.location.href = url.toString();
            }
        })).open();
    }

    tabActual.onclick = () => switchTab('ACTUAL');
    tabTarget.onclick = () => switchTab('TARGET');
</script>
@endsection
