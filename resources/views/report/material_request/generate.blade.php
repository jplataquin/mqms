@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <table>
            @foreach($report as $contract_item_id => $contract_item)
            

                <tr>
                    <td colspan="2">1 {{ $contract_item_arr[$contract_item_id]->item_code }} {{$contract_item_arr[$contract_item_id]->description}}</td>
                </tr>

                @foreach($contract_item as $component_id => $component)
                    <tr>
                        <td colspan="2">2 {{ $component_id }}</td>
                    </tr>

                    @foreach($component as $component_item_id => $component_item)
                        <tr>
                            <td colspan="2">3 {{$component_item_arr[$component_item_id]->name}}</td>
                        </tr>

                        @foreach($component_item as $material_quantity_id => $result)
                            <tr>
                                @php 
                                    //$material_item = $material_item_arr[ $material_quantity_arr[$material_quantity_id]->material_item_id ];
                                @endphp
                                <td colspan="2">4</td>
                            </tr>
                            <tr>
                                <td>Budget</td>
                                <td>{{$result['budget_quantity']}}</td>
                            </tr>
                            <tr>
                                <td>Request</td>
                                <td>{{$result['request_quantity']}}</td>
                            </tr>
                            <tr>
                                <td>PO</td>
                                <td>{{$result['po_quantity']}}</td>
                            </tr>
                        @endforeach

                    @endforeach

                @endforeach

            @endforeach
        </table>
    </div>
</div>
@endsection