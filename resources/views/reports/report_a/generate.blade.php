@extends('layouts.app')

@section('content')
<div class="container">
<h5>Reports » A » Generate</h5>
<hr>

<table class="table bordered">
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
            <th>Component</th>
            <td>{{$component->name}}</td>
        </tr>
        <tr>
            <th>Date Scope</th>
            <td></td>
        </tr>
    </tbody>
</table>

<hr>

    @foreach($component_items as $component_item)
    <div class="mb-5">
    <table class="table bordered w-100">
        <tbody>
            <tr>
                <td colspan="3">
                    <h5>{{$component_item->name}}</h5>
                </td>
            </tr>
            <tr>
                <th>Budget</th>
                <th>Requested</th>
                <th>PO</th>
            </tr>
            <tr>
                <td>
                    {{$component_item->quantity}} {{$component_item->unit}} 
                </td>
                <td>
                    @if( isset($total_requested[$component_item->id]) )
                        {{$total_requested[$component_item->id]->total}} {{$total_requested[$component_item->id]->unit}}
                    @else
                        0 {{$component_item->unit}}
                    @endif
                </td>
                <td class="text-center">
                    @if( isset($total_po[$component_item->id]) )
                        {{$total_po[$component_item->id]->total}} {{$total_po[$component_item->id]->unit}}
                    @else
                        0 {{$component_item->unit}}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table bordered w-100">
        <thead>
            <tr>
                <th>Material Item</th>
                <th>Total Quantity</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase_order_item[$component_item->id] as $poi)
                <tr>
                    <td>
                        {{$material_items[$poi->material_item_id]->brand}} {{$material_items[$poi->material_item_id]->name}} {{$material_items[$poi->material_item_id]->unit_packaging_specification}}
                    </td>
                    <td>
                        {{ number_format($poi->total_quantity,2) }}
                    </td>
                    <td>
                        Php {{ number_format($poi->total_price,2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endforeach

</div>

<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

</script>
@endsection
