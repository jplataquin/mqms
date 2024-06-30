
<page>
    <div>

        <div>
            <h3>
                {{$project->name}} - ( {{$section->name}} )
            </h3>
            <hr>
            <table border="1" style="border-collapse: collapse">
                   
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
        <table>
            <tr>
                <th style="width:50%; background-color:#add8e6">{{$item->name}}</th>
                <th style="width:50%; background-color:#add8e6"> {{$item->quantity}} {{$item->unit}} </th>
            </tr>
        </table>
        <table style="border: solid 1px #000000; border-collapse: collapse">
           
                <tr>
                    <th width="50%">&nbsp;</th>
                    <th>Equivalent</th>
                    <th>Quantity</th>
                    <th>Total</th>
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