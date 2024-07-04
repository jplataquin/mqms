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
    <div class="mb-5 border border-primary">
    
        <table class="table bordered w-100">
            <tbody>
                <tr>
                    <td>
                        <h5>{{$component_item->name}}</h5>
                    </td>
                </tr>
            <tbody>
        </table>

        <div class="row mt-3 mb-3">
            <div class="col-4 text-center">
                
                <h5>
                    {{$component_item->quantity}} {{$component_item->unit}} 
                    <br>
                    Budget
                </h5>
            </div>

            <div class="col-4 text-center">
                
                <h5>
                    
                    @if( isset($total_requested[$component_item->id]) )
                        
                        {{$total_requested[$component_item->id]->total}} {{$total_requested[$component_item->id]->unit}} 

                        
                    @else
                        0 {{$component_item->unit}}
                    @endif
                    <br>
                    Requested 
                    @if( isset($total_po[$component_item->id]) )
                        @php 
                            $percentRequested = ($total_requested[$component_item->id]->total / $component_item->quantity) * 100;
                        @endphp
                        ({{ $percentRequested }}%)
                    @else
                        (0%)
                    @endif
                </h5>
            </div>
                    
            <div class="col-4 text-center">
                
                <h5>
                    @if( isset($total_po[$component_item->id]) )

                        {{$total_po[$component_item->id]->total}} {{$total_po[$component_item->id]->unit}}
                    @else
                        0 {{$component_item->unit}}
                    @endif
                    <br>
                    PO
                    @if( isset($total_po[$component_item->id]) )
                        @php 
                            $percentPO = ($total_po[$component_item->id]->total / $component_item->quantity) * 100;
                        @endphp
                        ({{ $percentPO }}%) 
                    @else
                        (0%)
                    @endif
                </h5>
            </div>
             
        </div>
    <table class="table bordered w-100">
        <thead>
            <tr>
                <th colspan="3" class="text-center">PO items</th>
            </tr>
        </thead>
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
