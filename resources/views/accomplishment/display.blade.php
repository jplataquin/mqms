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
                        Display Record
                        </span>
                        <i class="ms-2 bi bi-display"></i>
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
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-container">
            <div class="form-header text-center mb-3">
                Accomplishment Registry Record Details
            </div>
            <div class="form-body">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Record ID</label>
                            <input type="text" class="form-control" disabled value="{{ STR_PAD($accomplishment->id, 6, '0', STR_PAD_LEFT) }}"/>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Type</label>
                            <input type="text" class="form-control" disabled value="{{ $accomplishment->type }}"/>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Entry Date</label>
                            <input type="text" class="form-control" disabled value="{{ $accomplishment->entry_data ? $accomplishment->entry_data->format('Y-m-d') : 'N/A' }}"/>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Quantity Accomplished</label>
                            <input type="text" class="form-control" disabled value="{{ number_format($accomplishment->quantity, 2) }} {{ $component->unit_text }}"/>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea class="form-control" rows="4" disabled>{{ $accomplishment->remarks }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Created By</label>
                            <input type="text" class="form-control" disabled value="{{ $accomplishment->CreatedBy ? $accomplishment->CreatedBy->name : 'System' }}"/>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label fw-semibold">Created At</label>
                            <input type="text" class="form-control" disabled value="{{ $accomplishment->created_at ? $accomplishment->created_at->format('Y-m-d H:i:s') : 'N/A' }}"/>
                        </div>
                    </div>
                </div>

                @if($accomplishment->updated_by)
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-label fw-semibold">Updated By</label>
                                <input type="text" class="form-control" disabled value="{{ $accomplishment->UpdatedBy ? $accomplishment->UpdatedBy->name : 'N/A' }}"/>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-label fw-semibold">Updated At</label>
                                <input type="text" class="form-control" disabled value="{{ $accomplishment->updated_at ? $accomplishment->updated_at->format('Y-m-d H:i:s') : 'N/A' }}"/>
                            </div>
                        </div>
                    </div>
                @endif
            
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <a href="/accomplishment/component/{{$component->id}}" class="btn btn-secondary px-4" id="backBtn">Back to Component</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
