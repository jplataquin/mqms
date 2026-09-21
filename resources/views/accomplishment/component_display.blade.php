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

    <div class="container px-0" id="list">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 text-secondary fw-bold"><i class="bi bi-list-stars ms-1"></i> Accomplishment Registry Records</h5>
            </div>
            <div class="card-body p-0">
                @if($accomplishments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-plus display-1 text-secondary opacity-50 mb-3 d-block"></i>
                        <p class="fs-5 mb-0">No accomplishment records found for this component.</p>
                        <small>Click "Add Registry Entry" to create your first record.</small>
                    </div>
                @else
                    <div class="table-responsive">
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
                            <tbody>
                                @foreach($accomplishments as $item)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $item->entry_data ? $item->entry_data->format('Y-m-d') : 'N/A' }}</td>
                                        <td>
                                            @if($item->type == 'ACTUAL')
                                                <span class="badge bg-success text-white">ACTUAL</span>
                                            @else
                                                <span class="badge bg-info text-dark">TARGET</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($item->quantity, 2) }} {{ $component->unit_text }}</td>
                                        <td><span class="text-wrap d-inline-block" style="max-width: 300px;">{{ $item->remarks }}</span></td>
                                        <td>{{ $item->CreatedBy ? $item->CreatedBy->name : 'System' }}</td>
                                        <td class="pe-4 text-muted small">{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
</div>
@endsection
