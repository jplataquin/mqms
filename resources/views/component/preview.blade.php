<style>
    .table{
        border-collapse: collapse;
    }

    .table td{
        padding: 3px;
    }

    .table th{
        padding: 3px;
    }

    .text-center{
        text-align:center;
    }
</style>
<page>
    <div>
        <div style="margin-bottom: 10px">
            <h3>
                {{$project->name}} - ( {{$section->name}} )
            </h3>
            <hr>
            <table border="1" class="table">
                   
                    <tr>
                        <th style="width:10%">Component</th>
                        <td style="width:90%">{{$component->name}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$component->status}}</td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td>{{$component->createdByUser()->name}} {{$component->created_at}}</td>
                    </tr>
                    <tr>
                        <th>Updated By</th>
                        <td>{{$component->updatedByUser()->name}} {{$component->updated_at}}</td>
                    </tr>
                    <tr>
                        <th>Hash</th>
                        <td>{{$hash}}</td>
                    </tr>
            </table>
        </div>

        @foreach($componentItems as $item)
        

        <nobreak>
            <div style="margin-bottom:10px">
                <table border="1" class="table" style="margin-botom:3px">
                    <tr>
                        <th style="width:50%;background-color:#add8e6" colspan="2">{{$item->name}}</th>
                        <th style="width:50%;background-color:#add8e6" class="text-center">{{$item->quantity}} {{$item->unit}}</th>
                        <th style="width:50%;background-color:#add8e6" class="text-center">P {{number_format($item->budget_price,2) }}</th>
                    </tr>
                </table>
                <table border="1" class="table" style="margin-bottom:5px">
                        

                        <tr>
                            <th style="width:40%" class="text-center">Material</th>
                            <th style="width:20%" class="text-center">Equivalent</th>
                            <th style="width:20%" class="text-center">Quantity</th>
                            <th style="width:20%" class="text-center">Total</th>
                        </tr>
                        
                        @foreach($item->materialQuantities as $mq)
                        <tr>
                            <td>
                                {{$materialItems[$mq->material_item_id]->name }} 
                                {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                                {{$materialItems[$mq->material_item_id]->brand }} 
                            </td>
                            <td class="text-center">
                                {{$mq->equivalent}} {{$item->unit}}
                            </td>
                            <td class="text-center">
                                {{$mq->quantity}}
                            </td>
                            <td class="text-center">
                                {{$mq->equivalent * $mq->quantity}} {{$item->unit}}
                            </td>
                        </tr>
                        @endforeach

                </table>
            </div>
        </nobreak>

        <nobreak>
            <div style="margin-bottom:10px">
                <table border="1" class="table" style="margin-bottom:5px">
                    <tr>
                        <th style="width:50%; background-color:#add8e6">{{$item->name}}</th>
                        <th style="width:50%; background-color:#add8e6" class="text-center"> {{$item->quantity}} {{$item->unit}} / P{{number_format($item->budget_price,2) }}</th>
                    </tr>
                </table>
                <table border="1" class="table" style="margin-bottom:5px">
                
                        <tr>
                            <th style="width:40%" class="text-center">Material</th>
                            <th style="width:20%" class="text-center">Equivalent</th>
                            <th style="width:20%" class="text-center">Quantity</th>
                            <th style="width:20%" class="text-center">Total</th>
                        </tr>
                        
                        @foreach($item->materialQuantities as $mq)
                        <tr>
                            <td>
                                {{$materialItems[$mq->material_item_id]->name }} 
                                {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                                {{$materialItems[$mq->material_item_id]->brand }} 
                            </td>
                            <td class="text-center">
                                {{$mq->equivalent}} {{$item->unit}}
                            </td>
                            <td class="text-center">
                                {{$mq->quantity}}
                            </td>
                            <td class="text-center">
                                {{$mq->equivalent * $mq->quantity}} {{$item->unit}}
                            </td>
                        </tr>
                        @endforeach

                </table>
            </div>
        </nobreak>
        @endforeach
    

    </div>
</page>