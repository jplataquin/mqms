@extends('layouts.app')

@section('content')
<div class="container">

        <div>
            <h3>
                {{$project->name}} - ( {{$section->name}} )
            </h3>
            <hr>
            <table class="table border">
                <tbody>
                    
                    <tr>
                        <th width="10%">Component</th>
                        <td>{{$component->name}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$component->status}}</td>
                    </tr>
                    <tr>
                        <th>Hash</th>
                        <td>{{$hash}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @foreach($componentItems as $item)
        <table class="border table">
            <thead>
                <tr>
                    <th class="text-center" style="background-color:#add8e6">{{$item->name}}</th>
                    <th class="text-center" style="background-color:#add8e6"> {{$item->quantity}} {{$item->unit}} </th>
                </tr>
            </thead>
        </table>
        <table class="ms-3 border table">
            <thead>
                <tr>
                    <th width="50%">&nbsp;</th>
                    <th>Equivalent</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                
                @foreach($item->materialQuantities as $mq)
                <tr>
                    <td>
                        {{$materialItems[$mq->material_item_id]->name }} 
                        {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                        {{$materialItems[$mq->material_item_id]->brand }} 
                    </td>
                    <td>
                        {{$mq->equivalent}} {{$item->unit}}
                    </td>
                    <td>
                        {{$mq->quantity}}
                    </td>
                    <td>
                        {{$mq->equivalent * $mq->quantity}} {{$item->unit}}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </li>
        @endforeach
    

</div>

@endsection