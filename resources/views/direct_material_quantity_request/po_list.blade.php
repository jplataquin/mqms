@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/direct_material_quantity_request/{{$request->id}}">
                        <span>
                        Direct Material Request
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            PO List
                        </span>
                        <i class="ms-2 bi bi-display"></i>		
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-5">
            <div class="folder-form-tab">
                PO List
            </div>
            <div class="folder-form-body">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Project</label>
                            <input type="text" value="{{$project ? $project->name : 'None'}}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Section</label>
                            <input type="text" value="{{$section ? $section->name : 'None'}}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Direct Material Request ID</label>
                            <input type="text" value="{{ str_pad($request->id,6,0,STR_PAD_LEFT) }}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" value="{{ $request->status }}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                </div>
                @if($request->description)
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Purpose / Description</label>
                            <input type="text" value="{{ $request->description }}" class="form-control" disabled="true"/>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-lg-12">
                <hr>
                <h3>Total PO Count: {{$total_count}}</h3>
                <hr>
            </div>
        </div>
        <div class="row mb-3" hx-boost="true" hx-select="#content" hx-target="#main">
            <div class="col-lg-4 mb-4 text-center">
                <h4>Draft ({{count($draft)}})</h4>
                <div class="list-group">
                    @forelse($draft as $draft_po)
                        <a class="list-group-item list-group-item-action" href="/direct_purchase_order/{{$draft_po->id}}">PO#{{str_pad($draft_po->id,6,0,STR_PAD_LEFT)}}</a>
                    @empty
                        <div class="list-group-item text-muted small py-2">No Draft POs</div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4 mb-4 text-center">
                <h4>Pending ({{count($pending)}})</h4>
                <div class="list-group">
                    @forelse($pending as $pend_po)
                        <a class="list-group-item list-group-item-action" href="/direct_purchase_order/{{$pend_po->id}}">PO#{{str_pad($pend_po->id,6,0,STR_PAD_LEFT)}}</a>
                    @empty
                        <div class="list-group-item text-muted small py-2">No Pending POs</div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4 mb-4 text-center">
                <h4>Approved ({{count($approved)}})</h4>
                <div class="list-group">
                    @forelse($approved as $aprv_po)
                        <a class="list-group-item list-group-item-action" href="/direct_purchase_order/{{$aprv_po->id}}">PO#{{str_pad($aprv_po->id,6,0,STR_PAD_LEFT)}}</a>
                    @empty
                        <div class="list-group-item text-muted small py-2">No Approved POs</div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4 mb-4 text-center">
                <h4>Rejected ({{count($rejected)}})</h4>
                <div class="list-group">
                    @forelse($rejected as $rejc_po)
                        <a class="list-group-item list-group-item-action" href="/direct_purchase_order/{{$rejc_po->id}}">PO#{{str_pad($rejc_po->id,6,0,STR_PAD_LEFT)}}</a>
                    @empty
                        <div class="list-group-item text-muted small py-2">No Rejected POs</div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4 mb-4 text-center">
                <h4>Void ({{count($void)}})</h4>
                <div class="list-group">
                    @forelse($void as $void_po)
                        <a class="list-group-item list-group-item-action" href="/direct_purchase_order/{{$void_po->id}}">PO#{{str_pad($void_po->id,6,0,STR_PAD_LEFT)}}</a>
                    @empty
                        <div class="list-group-item text-muted small py-2">No Void POs</div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4 mb-4 text-center">
                <h4>Deleted ({{count($deleted)}})</h4>
                <div class="list-group">
                    @forelse($deleted as $del_po)
                        <a class="list-group-item list-group-item-action" href="/direct_purchase_order/{{$del_po->id}}">PO#{{str_pad($del_po->id,6,0,STR_PAD_LEFT)}}</a>
                    @empty
                        <div class="list-group-item text-muted small py-2">No Deleted POs</div>
                    @endforelse
                </div>
            </div>

        </div>

        <div class="row mt-4 mb-5">
            <div class="col-12 text-end">
                <a href="/direct_material_quantity_request/{{$request->id}}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Direct Material Request
                </a>
            </div>
        </div>
    </div>
    <script type="module">
    </script>
</div>
@endsection
