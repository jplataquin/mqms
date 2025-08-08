<<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en-US"> <!--<![endif]-->
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="pingback" href="https: //domainname.com/xmlrpc.php" />
        <title></title>

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
                width:100%;
                min-width:100%;
            }

            td{
                padding: 3px;
            }

            .mb-10{
                margin-bottom:10px;
            }

            .header{
                font-size:
            }


            .bg-grey{
                background-color:#ccc;
            }

            .font-10{
                font-size:10px;
            }

            @media print {
                .whole {
                    break-inside: avoid;
                }
            }
        
        </style>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        
     

         @foreach(['Supplier','Site','Accounting'] as $copy)
            <div class="whole mb-10">
                <table class="table" border="1">
                    <tr>
                        <td colspan="4" class="text-center bold" style="">
                            <img src="/storage/sys_images/header.png" style="width:500px"/>
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
                                    <td class="text-left bold">Project</td>
                                    <td colspan="3">
                                        {{$project->name}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left bold">Datetime</td>
                                    <td colspan="3" style="font-size:12px">
                                        
                                        [Created]
                                        {{$purchase_order->created_at}}
                                        | 
                                        [Approved]
                                        {{$purchase_order->approved_at}}
                                        | 
                                        [Generated] 
                                        {{$current_datetime}}
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
                            @php 
                            
                            $item_name = $materialItemArr[ $item->material_item_id]->brand.' '.$materialItemArr[ $item->material_item_id]->name.' '.$materialItemArr[ $item->material_item_id]->specification_unit_packaging;
                            $item_name = Str::wordWrap($item_name, 50, "<br>", false);

                            @endphp
                            <td>{!! $item_name !!}</td>
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
            </div>
         @endforeach
        
        <div class="whole mb-10">
             <table class="table" border="1">
                <tr>
                    <td colspan="4" class="text-center bold" style="">
                        <img src="/storage/sys_images/header.png" style="width:500px"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="text-center bold" style="background-color:#ccc">
                        Purchase Order ( Warehouse Copy )
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding:3px">
                        <table class="table" style="margin:auto" border="1">
                            <tr>
                                <td class="text-left bold" style="width:15%">PO#</td>
                                <td style="width:20%">{{ str_pad($purchase_order->id,6,0,STR_PAD_LEFT) }}</td>
                                <td class="text-left bold" rowspan="2"  style="width:15%">Supplier</td>
                                <td style="width:48%" rowspan="2"> {{$supplier->name}}</td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Mat. Req #</td>
                                <td>{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}</td>
                              
                            </tr>
                            <tr>
                                <td class="text-left bold">Project</td>
                                <td colspan="3">
                                    {{$project->name}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Section</td>
                                <td colspan="3">
                                    {{$section->name}}
                                </td>
                            </tr>
                                 <tr>
                                <td class="text-left bold">Contract Item</td>
                                <td colspan="3">
                                    {{$contract_item->name}}
                                </td>
                            </tr>
                                 <tr>
                                <td class="text-left bold">Component</td>
                                <td colspan="3">
                                    {{$component->name}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left bold">Datetime</td>
                                <td colspan="3" style="font-size:12px">
                                    
                                    [Created]
                                    {{$purchase_order->created_at}}
                                    | 
                                    [Approved]
                                    {{$purchase_order->approved_at}}
                                    | 
                                    [Generated] 
                                    {{$current_datetime}}
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
                
                
                @foreach($items as $i => $item)
                    <tr>
                        <td colspan="4">{{$i+1}}.) {!! $item_name !!}</td>
                    </tr>
                    <tr>
                        <td class="text-center bold">Quantity</td>
                        <td class="text-center bold">Received</td>
                        <td class="text-center bold">Rejected</td>
                        <td class="text-center bold">Date</td>
                    </tr>
                    <tr>
                        @php 
                        
                            $item_name = $materialItemArr[ $item->material_item_id]->formatted_name;
                            $item_name = Str::wordWrap($item_name, 50, "<br>", false);

                        @endphp
                        <td class="text-center">{{number_format($item->quantity,2)}}</td>
                        <td class="text-right">&nbsp;</td>
                        <td class="text-right">&nbsp;</td>
                    </tr>

                    @for($i = 0; $i <= 2; $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor

                        <tr>
                            <td class=""></th>
                            <td class="bold font-10">Total: </td>
                            <td class="bold font-10">Total: </td>
                            <td class="bold font-10">Diff: </td>
                        </tr>
                        <tr>
                            <td class="bg-grey" colspan="4">&nbsp;</td>
                        </tr>
                @endforeach    
              
       
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
                    <td class="text-center" style="padding-left:30px">
                        <br>
                        __________________________
                        <br>
                        <label class="bold">Validated By</label>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>

    
        
           