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
                    <td class="bg-teal">
                        <h5>{{$component_item->name}}</h5>
                    </td>
                </tr>
            <tbody>
        </table>

        <div class="row mt-3 mb-3">
            <div class="col-4 text-center">
                
                <h5>
                    Budget
                    <br>
                    {{$component_item->quantity}} {{$component_item->unit}} 
                    <br>
                   
                </h5>
            </div>

            <div class="col-4 text-center">
                
                <h5>
                    Requested
                    <br>
                    @if( isset($total_requested[$component_item->id]) )
                        
                        {{$total_requested[$component_item->id]->total}} {{$total_requested[$component_item->id]->unit}} 

                        
                    @else
                        0 {{$component_item->unit}}
                    @endif
                   
                </h5>
                
                    @if( isset($total_po[$component_item->id]) )
                        @php 
                            $percentRequested = ($total_requested[$component_item->id]->total / $component_item->quantity) * 100;
                        @endphp
                        ({{ round($percentRequested,2) }}%)
                    @else
                        (0%)
                    @endif
            </div>
                    
            <div class="col-4 text-center">
                
                <h5>
                    PO
                    <br>
                    @if( isset($total_po[$component_item->id]) )

                        {{$total_po[$component_item->id]->total}} {{$total_po[$component_item->id]->unit}}
                    @else
                        0 {{$component_item->unit}}
                    @endif
                    
                </h5>

                    @if( isset($total_po[$component_item->id]) )
                        @php 
                            $percentPO = ($total_po[$component_item->id]->total / $component_item->quantity) * 100;
                        @endphp
                        ({{ round($percentPO,2) }}%) 
                    @else
                        (0%)
                    @endif
            </div>
             
        </div>
        <div class="bg-secondary w-100">&nbsp;</div>
        <table class="table bordered w-100">
        <thead>
            <tr>
                <th>Material Item</th>
                <th>Total Quantity</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grand_total_price = 0;
            @endphp
            @foreach($purchase_order_item[$component_item->id] as $poi)
                <tr>
                    <td>
                        {{$material_items[$poi->material_item_id]->brand}} {{$material_items[$poi->material_item_id]->name}} {{$material_items[$poi->material_item_id]->specification_unit_packaging}}
                    </td>
                    <td>
                        {{ number_format($poi->total_quantity,2) }}
                    </td>
                    <td>
                        Php {{ number_format($poi->total_price,2) }}

                        @php 
                            $grand_total_price = $grand_total_price + $poi->total_price;
                        @endphp
                    </td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td>
                    <strong>Php {{number_format($grand_total_price,2)}}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
    @endforeach

</div>

<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

</script>
@endsection
