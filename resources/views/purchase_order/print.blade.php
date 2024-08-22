
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
        }

        td{
            padding: 3px;
        }

        
    </style>
    
    <page>
        <div>
            @foreach(['Supplier','Site','Accounting','Warehouse'] as $copy)
            <nobreak>
            <table class="table" border="1">
                <tr>
                    <td colspan="4" class="text-center bold" style="">
                        <img src="storage/sys_images/header.png" style="width:500px"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="text-center bold">
                        Purchase Order - {{$copy}} Copy
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding:0px">
                        <table class="table" style="margin-top:2px; margin-bottom:2px" border="1">
                            <tr>
                                <td class="text-left bold" style="width:20%">PO#</td>
                                <td style="width:30%">{{ str_pad($purchase_order->id,6,0,STR_PAD_LEFT) }}</td>
                                <td class="text-left bold"  style="width:18%">Supplier</td>
                                <td style="width:30%">{{$supplier->name}}</td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Mat. Req #</td>
                                <td>{{$material_quantity_request->id}}</td>
                                <td class="text-left bold" >Payment Terms</td>
                                <td>{{$payment_term->text}}</td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Date Generated</td>
                                <td>{{$current_datetime}}</td>
                                <td class="text-left bold">Contact Person</td>
                                <td>{{$supplier->primary_contact_person}}</td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Date Approved</td>
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
                        </table>
                    </td>
                </tr>
                <tr>
                    <th class="text-center bold" colspan="4">
                        Items
                    </th>
                </tr>
                <tr>
                    <td class="text-center bold">Material</td>
                    <td class="text-center bold">Quantity</td>
                    <td class="text-center bold">Price</td>
                    <td class="text-center bold">Total</td>
                </tr>
                @php $subtotal = 0; @endphp
                @foreach($items as $item)
                    <tr>
                        <td>{{$materialItemArr[ $item->material_item_id]->brand}} {{$materialItemArr[ $item->material_item_id]->name}} {{$materialItemArr[ $item->material_item_id]->specification_unit_packaging}}</td>
                        <td class="text-center">{{number_format($item->quantity,2)}}</td>
                        <td class="text-right">P {{number_format($item->price,2)}}</td>
                        <td class="text-right">P {{number_format($item->quantity*$item->price,2)}}</td>
                    </tr>
                
                @php $subtotal = $subtotal + ($item->quantity*$item->price); @endphp
                @endforeach    
                <tr>
                    <td colspan="2"></td>
                    <th class="text-right" style="padding-right:5px;padding-top:3px">Sub Total</th>
                    <td class="text-right">P {{number_format($subtotal,2)}}</td>
                </tr>
                @php $grandtotal = $subtotal; @endphp
                @foreach($extras as $extra)

                    <tr>
                        <td colspan="2"></td>
                        <th class="text-right" style="padding-right:5px;padding-top:3px">{{$extra->text}}</th>
                        <td class="text-right">P {{ number_format($extra->value,2) }}</td>
                    </tr>
                        
                            
                    @php $grandtotal = $grandtotal + $extra->value; @endphp
                @endforeach
                <tr>
                    <td colspan="2"></td>
                    <th class="text-right" style="padding-right:5px;padding-top:3px">Grand Total</th>
                    <td class="text-right">P {{number_format($grandtotal,2)}}</td>
                </tr>
            </table>
           
            <table class="table" style="margin-left:50px; margin-top:40px; margin-bottom:20px">
                <tr>
                    <td class="text-center bold" style="padding-right:30px">
                        __________________________
                        <br>
                        Prepared By
                    </td>
                    <td class="text-center bold">
                        __________________________
                        <br>
                        Approved By
                    </td>
                    <td class="text-center bold" style="padding-left:30px">
                        __________________________
                        <br>
                        Received By
                    </td>
                </tr>
            </table>
            </nobreak>
            @endforeach
        </div>
    </page>