@extends('layouts.app')

@section('content')
<style>

    .contract_item{
        background-color: #c5d8f0 !important;
        position:sticky !important;
        top:40px;
    }

    .component{
        background-color: #eff0c5 !important;
    }
</style>
<div id="content">
    <div class="container">
        <table class="table">
            @foreach($report as $contract_item_id => $contract_item)
            

                <tr>
                    <th colspan="2" class="contract_item">{{ $contract_item_arr[$contract_item_id]->item_code }} {{$contract_item_arr[$contract_item_id]->description}}</th>
                </tr>
                
                @foreach($contract_item as $component_id => $component)
                    
                    <tr>
                        <td colspan="2" style="padding-left:3em" class="component">{{ $component_arr[ $component_id ]->name }}</td>
                    </tr>

                    @foreach($component as $component_item_id => $component_item)
                        <tr>
                            <th colspan="2" style="padding-left:6em" class="component_item">{{$component_item_arr[$component_item_id]->name}}</th>
                        </tr>

                        @foreach($component_item as $material_quantity_id => $result)
                            <tr>
                                @php 
                                    $material_item = $material_item_arr[ $material_quantity_arr[$material_quantity_id]->material_item_id ];
                                @endphp
                                <td colspan="2" style="padding-left:9em" class="material_item">4 {{ $material_item->formatted_name() }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left:12em">Budget</td>
                                <td>{{ number_format($result['budget_quantity'],2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left:12em">Request</td>
                                <td>{{ number_format($result['request_quantity'],2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left:12em">PO</td>
                                <td>{{ number_format($result['po_quantity'],2) }}</td>
                            </tr>
                        @endforeach

                    @endforeach

                @endforeach

            @endforeach
        </table>
    </div>
</div>
@endsection