
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
                    <td colspan="3" class="text-center bold" style="">
                        <img src="storage/sys_images/header.png" style="width:500px"/>
                    </td>
                    <td class="text-center" valign="middle">
                        <qrcode value="{{json_encode(['t'=>'MR','id'=>$material_quantity_request->id])}}" ec="H" style="width: 20mm; background-color: white; color: black;"></qrcode>
        
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="text-center bold" style="background-color:#ccc">
                        Purchase Order ( {{$copy}} Copy )
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding:3px">
                        <table class="table" style="margin:auto" border="1">
                            <tr>
                                <td class="text-left bold" style="width:15%">PO#</td>
                                <td style="width:20%">{{ str_pad($purchase_order->id,6,0,STR_PAD_LEFT) }}</td>
                                <td class="text-left bold"  style="width:15%">Supplier</td>
                                <td style="width:48%">{{$supplier->name}}</td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Mat. Req #</td>
                                <td>{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}</td>
                                <td class="text-left bold" >Payment Terms</td>
                                <td>{{$payment_term->text}}</td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Datetime</td>
                                <td colspan="3" style="font-size:11px">
                                    
                                    Created: {{$purchase_order->created_at}}
                                    <span class="bold"> / </span>
                                    Approved: {{$purchase_order->approved_at}}
                                    <span class="bold"> / </span>
                                    Generated: {{$current_datetime}}
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="text-left bold">Project</td>
                                <td colspan="3">
                                    {{$project->name}}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th class="text-center bold" colspan="4" style="background-color:#ccc;width:100%">
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
                    <td class="text-center" style="padding-right:30px">
  
                        {{$purchase_order->CreatedByUser()->name}}
                        <br>
                        __________________________
                        <br>
                        <label class="bold">Created By</label>
                    </td>
                    <td class="text-center">
                        
                        {{$purchase_order->ApprovedByUser()->name}}
                        <br>
                        __________________________
                        <br>
                        <label class="bold">Approved By</label>
                    </td>
                    <td class="text-center" style="padding-left:30px">
                        <br>
                        __________________________
                        <br>
                        <label class="bold">Received By</label>
                    </td>
                </tr>
            </table>
            </nobreak>
            @endforeach
        </div>
    </page>