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

   
        @foreach($data as $supplier_id => $d)
        <div class="mb-5">

            <h2>{{$d['supplier']->name}}</h2>
        
            <table class="table w-100">
                <tr>
                    <th>Material Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
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
    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection