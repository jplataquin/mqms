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

    <div class="container">
        <table class="table border" id="tableResponsive" style="display: none;">
            <thead>
                <tr>
                    <th>Entry Date</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Remarks</th>
                    <th>User</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody id="list">
            </tbody>
        </table>
    </div>

    <div class="row">
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
            let formattedQty = parseFloat(item.quantity).toFixed(2) + ' {{ $component->unit_text }}';

            let row = t.tr({class: 'selectable-div'}, () => {
                t.td(displayEntryData);
                t.td(item.type);
                t.td(formattedQty);
                t.td(item.remarks || '');
                t.td(item.creator_name || 'System');
                t.td(displayCreatedAt);
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
                tableResponsive.style.display = 'none';
                showMoreBtn.style.display = 'none';
                return;
            }

            if (page === 1) {
                tableResponsive.style.display = 'table';
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
