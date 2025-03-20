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

        <h2 class="mb-3">Per Supplier</h2>
        @foreach($per_supplier as $supplier_id => $d)
        <div class="mb-5">

            <h3 class="mb-3">{{$d['supplier']->name}}</h3>
        
            <table class="table w-100">
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
                @endforeach
            </table>
        </div>
        @endforeach
   

    <hr>


        <h2 class="mb-3">Per Material</h2>
        <div>
            <table class="table w-100">
                <tr>
                    <th>Material Item</th>
                    <th class="text-center">Quantity</th>
                </tr>
                @foreach($per_material as $m)
                <tr>
                    <td>{{$m->MaterialItem->formatted_name}}</td>
                    <td class="text-center">{{ number_format($m->total_quantity,2) }}</td>
                </td>
                @endforeach
            </table>
        </div>
    </div>
    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection