<!DOCTYPE html>
    <head>
         <style>
            .text-left{
                text-align: left;
            }

            .text-center{
                text-align: center;
            }

            .text-right{
                text-align: right;
            }
            
            .bold{
                font-weight: bold;
            }

            .table{
                border-collapse:collapse;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: 10px;
            }

            td{
                padding: 3px;
            }

        </style>
    </head>
    <body>
        <div>
            @foreach(['Supplier','Site','Accounting','Warehouse'] as $copy)
            <table class="table" border="1">
                <tr>
                    <td colspan="4" class="text-center bold" style="width:100%">
                        Purchase Order
                    </td>
                </tr>
                <tr>
                    <td class="text-left bold">PO#</td>
                    <td>{{$purchase_order->id}}</td>
                    <td class="text-left bold">Supplier</td>
                    <td>{{$supplier->name}}</td>
                </tr>
                <tr>
                    <td class="text-left bold">Mat. Qty Req #</td>
                    <td>{{$material_quantity_request->id}}</td>
                    <td class="text-left bold">Payment Terms</td>
                    <td>{{$payment_term->text}}</td>
                </tr>
                <tr>
                    <td class="text-left bold">Copy</td>
                    <td>{{$copy}}</td>
                    <td class="text-left bold">Contact Person</td>
                    <td>{{$supplier->primary_contact_person}}</td>
                </tr>
                <tr>
                    <td class="text-left bold">Date</td>
                    <td>{{$purchase_order->approved_at}}</td>
                    <td class="text-left bold">Contact No:</td>
                    <td>{{$supplier->primary_contact_no}}</td>
                </tr>
                <tr>
                    <td class="text-left bold">Project</td>
                    <td>{{$project->name}}</td>
                    <td class="text-left bold">Section / Component </td>
                    <td>{{$section->name}} - {{$component->name}}</td>
                </tr>
                <tr>
                    <th class="text-center bold" colspan="4">
                        Items
                    </th>
                </tr>
                <tr>
                    <td class="bold">Material</td>
                    <td class="bold">Quantity</td>
                    <td class="bold">Price</td>
                    <td class="bold">Total</td>
                </tr>
                @php $subtotal = 0; @endphp
                @foreach($items as $item)
                <tr>
                    <td>{{$materialItemArr[ $item->material_item_id]->brand}} {{$materialItemArr[ $item->material_item_id]->name}} {{$materialItemArr[ $item->material_item_id]->specification_unit_packaging}}</td>
                    <td class="text-center">{{number_format($item->quantity,2)}}</td>
                    <td class="text-right">{{number_format($item->price,2)}}</td>
                    <td class="text-right">{{number_format($item->quantity*$item->price,2)}}</td>
                </tr>
                @php $subtotal = $subtotal + ($item->quantity*$item->price); @endphp
                @endforeach    
                <tr>
                    <td colspan="2"></td>
                    <th class="text-right">Sub Total</th>
                    <td class="text-right">{{number_format($subtotal,2)}}</td>
                </tr>

            </table>
            @endforeach
        </div>
    </body>
</html>