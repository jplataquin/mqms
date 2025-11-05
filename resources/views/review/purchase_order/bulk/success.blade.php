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

    <h1>{{$target_status_text}}</h1>

    <div class="mt-5">
        <form id="form">
            @foreach($pos as $project_id => $po_list)
                <div>
                    <h3>{{$project_arr[$project_id]->name}}</h3>

                    <div class="d-flex d-wrap p3">
                        @foreach($po_list as $item)
                            <input type="hidden" value="{{$item['po']->id}}" name="po[]"/>
                            <div class="border border-secondary">
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
                                    @if($po->status == 'APRV')
                                        PEND > <span class="text-success">APRV</span>
                                    @endif

                                    @if($po->status == 'REJC')
                                        PEND > <span class="text-danger">REJC</span>
                                    @endif

                                    @if($po->status == 'VOID')
                                        REVO > <span class="text-primary">VOID</span>
                                    @endif
                                </div>
                            </div>

                        @endforeach
                    </div>
                <div>
            @endforeach
        </form>

        <div class="text-end">
                <button class="btn btn-warning" id="revertBtn">Revert</button>
        </div>
    </div>

</div>
@endsection