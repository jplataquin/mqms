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
</style>
<page>
    <div>

        <div style="margin-bottm: 5px">
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
        <table border="1" class="table" style="margin-bottom:5px">
            <tr>
                <th style="width:50%; background-color:#add8e6">{{$item->name}}</th>
                <th style="width:50%; background-color:#add8e6"> {{$item->quantity}} {{$item->unit}} </th>
            </tr>
        </table>
        <table border="1" class="table">
           
                <tr>
                    <th width="25%">&nbsp;</th>
                    <th width="25%">Equivalent</th>
                    <th width="25%">Quantity</th>
                    <th width="25%">Total</th>
                </tr>
                
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

        </table>
        
        @endforeach
    

    </div>
</page>