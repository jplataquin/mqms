@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="#">
                        <span>
                        Report
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Purchase
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>
        <div class="mb-5">
            <h1 class="mb-3">Purchase Report</h1>
            <table class="record-table-horizontal">
                <tbody>
                    <tr>
                        <th>Project</th>
                        <td>{{$project->name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{$section->name}}</td>
                    </tr>
                    <tr>
                        <th>Contract Item</th>
                        <td>
                            @if($contract_item)
                                {{$contract_item->name}}
                            @else
                                *
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>
                            @if($component)
                                {{$component->name}}
                            @else
                                *
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Date Scope {{$from}}</th>
                        <td>
                            @if(!$from && !$to)
                                *
                            @else
                                @php 
                                    $from = $from ?? '*';
                                    $to   = $to ?? '*';
                                @endphp
                                    From: {{$from}} - To: {{$to}}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>No. of Supplier Filter</th>
                        <td>
                            {{$supplier_filter}}
                        </td>
                    </tr>
                    <tr>
                        <th>No. of Material Item</th>
                        <td>
                            {{$material_filter}}
                        </td>
                    </tr>
                </tbody>
            </table>    
        </div>
        
        <h2 class="mb-3 text-center">-- Per Supplier --</h2>

        @php 
            $supplier_grand_total = 0;
        @endphp

        @foreach($per_supplier as $supplier_id => $d)

        @php 
            $supplier_amount_total = 0;
        @endphp
        <div class="mb-5">

            <h3 class="mb-3">{{$d['supplier']->name}}</h3>
        
            <table class="table w-100 table-hover ">
                <tr>
                    <th>Material Item</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Total</th>
                </tr>
                @foreach($d['items'] as $po_item)
                <tr>
                    <td>
                        {{$po_item->MaterialItem->formatted_name}}
                    </td>
                    <td class="text-center">
                        {{ number_format($po_item->total_quantity,2) }}
                    </td>
                    <td class="text-end">
                        P {{$po_item->price}}
                    </td>
                    <td class="text-end">
                        P {{ number_format( ($po_item->total_quantity * $po_item->price), 2) }}
                    </td>
                </tr>

                @php
                    $supplier_amount_total += ($po_item->total_quantity * $po_item->price);
                @endphp

                @endforeach
                <tr>
                    <td colspan="3"></td>
                    <th class="text-end">
                        P {{ number_format($supplier_amount_total,2) }}
                    </th>
                </tr>
            </table>
        </div>

        @php 
            $supplier_grand_total += $supplier_amount_total;
        @endphp
        @endforeach
        <div class="mb-3 text-end">
            <h3>Grand Total: P {{number_format($supplier_grand_total,2)}} </h3>
        </div>

    <hr>


        <h2 class="mb-3 text-center">-- Per Material --</h2>
        <div>
            <table class="table w-100 table-hover table-striped">
                <tr>
                    <th>Material Item</th>
                    <th class="text-center">Quantity</th>
                </tr>
                @foreach($per_material as $m)
                <tr>
                    <td>{{$m->MaterialItem->formatted_name}}</td>
                    <td class="text-center">{{ number_format($m->total_quantity,2) }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <hr>

        <h2 class="mb-3 text-center">-- Per Purchase Order --</h2>
        <div>
            <table class="table w-100 table-hover table-striped">
                <thead>
                    <tr>
                        <th>Material Item</th>
                        <th class="text-center" style="width: 200px;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($per_po as $po_id => $po_data)
                    <tr class="table-secondary">
                        <th colspan="2" class="text-start">
                            PO # {{ $po_data['po_number'] }}
                        </th>
                    </tr>
                    @foreach($po_data['items'] as $item)
                    <tr>
                        <td class="ps-4">{{ $item->MaterialItem->formatted_name }}</td>
                        <td class="text-center">{{ number_format($item->total_quantity, 2) }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection