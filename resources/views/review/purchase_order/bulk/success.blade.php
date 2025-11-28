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
                <div>
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
@endsection