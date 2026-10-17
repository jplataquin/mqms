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
                <a href="/accomplishment/section/{{$section->id}}">
                    <span>
                       {{$section->name}}
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       {{$contract_item->item_code}}
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <table class="record-table-horizontal mb-3" hx-boost="true" hx-select="#content" hx-target="#main">
        <tbody>
            @php
                $isBehindTarget = false;
                $today = now()->startOfDay();
                $latestPastTarget = null;
                if (isset($accomplishments)) {
                    $latestPastTarget = $accomplishments->where('type', 'TARGET')->filter(function($acc) use ($today) {
                        $date = $acc->entry_data ? $acc->entry_data->startOfDay() : ($acc->created_at ? $acc->created_at->startOfDay() : null);
                        return $date && $date <= $today;
                    })->first();
                }

                if ($latestPastTarget) {
                    $targetQty = (float)$latestPastTarget->quantity;
                    $actualQty = $latestActual ? (float)$latestActual->quantity : 0;
                    if ($actualQty < $targetQty) {
                        $isBehindTarget = true;
                    }
                }
            @endphp
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
            <tr class="{{ $isBehindTarget ? 'table-danger' : '' }}">
                <th>Contract Item</th>
                <td>
                    <strong class="{{ $isBehindTarget ? 'text-danger' : '' }}">[{{$contract_item->item_code}}]</strong> 
                    <span class="{{ $isBehindTarget ? 'text-danger fw-semibold' : '' }}">{{$contract_item->description}}</span>
                    @if($isBehindTarget)
                        <span class="badge bg-danger ms-2">Behind Target</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Unit Price</th>
                <td>
                    PHP {{ number_format($contract_item->contract_unit_price, 2) }}
                </td>
            </tr>
            <tr>
                <th>Total Contract Amount</th>
                <td>
                    PHP {{ number_format($contract_item->contract_amount, 2) }}
                </td>
            </tr>
            <tr>
                <th>Item Weight</th>
                <td>
                    <strong class="text-info">{{ number_format(($contractItemWeight ?? 0) * 100, 4) }}%</strong>
                </td>
            </tr>
            <tr>
                <th>Total Quantity</th>
                <td>
                   {{ number_format($contract_item->contract_quantity, 2) }} {{$contract_item->contract_unit_text}}
                </td>
            </tr>
            <tr>
                <th>Latest Quantity</th>
                <td>
                   @if($latestActual)
                       @php
                           $percentage = $contract_item->contract_quantity > 0 ? ($latestActual->quantity / $contract_item->contract_quantity) * 100 : 0;
                           $formattedDate = $latestActual->entry_data ? $latestActual->entry_data->format('Y-m-d') : 'N/A';
                       @endphp
                       {{ number_format($latestActual->quantity, 2) }} {{$contract_item->contract_unit_text}} ({{ number_format($percentage, 0) }}%) as of {{ $formattedDate }}.
                   @else
                       -
                   @endif
                </td>
            </tr>
            <tr class="{{ $isBehindTarget ? 'table-danger' : '' }}">
                <th>Latest Actual (Weighted)</th>
                <td>
                   @if($latestActual)
                       @php
                           $actualPercentage = $contract_item->contract_quantity > 0 ? ($latestActual->quantity / $contract_item->contract_quantity) * 100 : 0;
                           $weightedActual = ($actualPercentage * ($contractItemWeight ?? 0));
                           $formattedDate = $latestActual->entry_data ? $latestActual->entry_data->format('Y-m-d') : 'N/A';
                       @endphp
                       <strong class="{{ $isBehindTarget ? 'text-danger' : 'text-primary' }}">{{ number_format($weightedActual, 4) }}%</strong>
                       <span class="{{ $isBehindTarget ? 'text-danger' : 'text-muted' }} ms-1">({{ number_format($latestActual->quantity, 2) }} {{$contract_item->contract_unit_text}} / {{ number_format($actualPercentage, 2) }}%) as of {{ $formattedDate }}</span>
                   @else
                       -
                   @endif
                </td>
            </tr>
            <tr>
                <th>Accomplished Amount</th>
                <td>
                   @if($latestActual)
                       @php
                           $accomplishedAmount = (float)$latestActual->quantity * (float)$contract_item->contract_unit_price;
                       @endphp
                       <strong class="text-primary">PHP {{ number_format($accomplishedAmount, 2) }}</strong>
                   @else
                       PHP 0.00
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
            let formattedQty = parseFloat(item.quantity).toFixed(2) + ' {{ $contract_item->contract_unit_text }}';
            let itemQty = parseFloat(item.quantity) || 0;
            let unitPrice = parseFloat('{{ $contract_item->contract_unit_price }}') || 0;
            let itemAmount = itemQty * unitPrice;
            let formattedAmount = 'PHP ' + itemAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let qtyClass = item.type === 'TARGET' ? 'fw-bold text-success' : 'fw-bold text-primary';

            let row = t.div({class: 'item-container fade-in'}, () => {
                t.div({class: 'item-header'}, `Entry Date: ${displayEntryData} [${item.type}]`);
                t.div({class: 'item-body'}, () => {
                    t.div({class: 'row'}, () => {
                        t.div({class: 'col-md-6'}, () => {
                            t.span({class: qtyClass}, `Quantity: ${formattedQty}`);
                        });
                        t.div({class: 'col-md-6 text-md-end'}, () => {
                            t.span({class: 'fw-semibold text-light'}, `Accomplished Amount: ${formattedAmount}`);
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
            contract_item_id: '{{ $contract_item->id }}',
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
            contract_item_id: '{{$contract_item->id}}',
            total_quantity: '{{$contract_item->contract_quantity}}',
            unit: '{{$contract_item->contract_unit_text}}',
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
