<!DOCTYPE html>
    <head>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div id="printable" style="font-size:11px"> 
            @foreach(['Supplier','Site','Accounting'] as $copy)
            <table width="100%" height="50%" style="margin-bottom:50px; margin-left:auto; margin-right:auto; border-collapse: collapse" border="1">
                
                    <tr>
                        <td colspan="4" align="center">
                           
                        <td>
                    </tr>
                    <tr>
                        <th colspan="4" align="center" colspan="4">
                            Purchase Order
                        </th>
                    </tr>
                    <tr>
                        <th align="left">PO#</th>
                        <td>{{$purchase_order->id}}</td>
                        <th align="left">Supplier</th>
                        <td>{{$supplier->name}}</td>
                    </tr>
                    <tr>
                        <th align="left">Mat. Qty Req #</th>
                        <td>{{$material_quantity_request->id}}</td>
                        <th align="left">Payment Terms</th>
                        <td>{{$payment_term->text}}</td>
                    </tr>
                    <tr>
                        <th align="left">Copy</th>
                        <td>{{$copy}}</td>
                        <th align="left">Contact Person</th>
                        <td>{{$supplier->primary_contact_person}}</td>
                    </tr>
                    <tr>
                        <th align="left">Date</th>
                        <td>{{$purchase_order->approved_at}}</td>
                        <th align="left">Contact No:</th>
                        <td>{{$supplier->primary_contact_no}}</td>
                    </tr>

                    <tr>
                        <th align="left">Project</th>
                        <td>{{$project->name}}</td>
                        <th align="left">Section / Component </th>
                        <td>{{$section->name}} - {{$component->name}}</td>
                    </tr>
                    
                    
                    <tr>
                        <th colspan="4">
                            Items
                        </th>
                    </tr>

                    <tr>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>

                    @php $subtotal = 0; @endphp
                    @foreach($items as $item)
                        <tr>
                            <td>{{$materialItemArr[ $item->material_item_id]->brand}} {{$materialItemArr[ $item->material_item_id]->name}} {{$materialItemArr[ $item->material_item_id]->specification_unit_packaging}}</td>
                            <td align="center">{{number_format($item->quantity,2)}}</td>
                            <td align="right">{{number_format($item->price,2)}}</td>
                            <td align="right">{{number_format($item->quantity*$item->price,2)}}</td>
                        </tr>
                        @php $subtotal = $subtotal + ($item->quantity*$item->price); @endphp
                    @endforeach
                    
                    <tr>
                        <td colspan="2"></td>
                        <th align="right" >Sub Total</th>
                        <td align="right">{{number_format($subtotal,2)}}</td>
                    </tr>

                    @php $grandtotal = $subtotal; @endphp
                    @foreach($extras as $extra)

                        <tr>
                            <td colspan="2"></td>
                            <th align="right">{{$extra->text}}</th>
                            <td align="right">{{ number_format($extra->value,2) }}</td>
                        </tr>
                        
                        
                        @php $grandtotal = $grandtotal + $extra->value; @endphp
                    @endforeach

                    <tr>
                        <td colspan="2"></td>
                        <th align="right">Grand Total</th>
                        <td align="right">{{number_format($grandtotal,2)}}</td>
                    </tr>

            </table>
        
        @endforeach

        </div>
        
        <link rel="stylesheet" type="text/css" href="/" media="print" />
        <script type="text/javascript">
            // import 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';

            // const doc = new jspdf.jsPDF({
            //     orientation: "portrait",
            // });

            // let printable = document.getElementById('printable');
            // console.log(printable.innerHTML);
            // doc.text(printable.innerHTML,10,10);
            // doc.save("two-by-four.pdf");
            // let text = document.getElementById('printable').innerHTML;
            // function printDiv({divId, title}) {
            //     let mywindow = window.open('', 'PRINT', 'height=650,width=900,top=100,left=150');

            //     mywindow.document.write(`<html><head><title>${title}</title>`);
            //     mywindow.document.write('</head><body >');
            //     mywindow.document.write(text);
            //     mywindow.document.write('</body></html>');

            //     mywindow.document.close(); // necessary for IE >= 10
            //     mywindow.focus(); // necessary for IE >= 10*/

            //     mywindow.print();
            //     mywindow.close();

            //     return true;
            //     }

            //     printDiv('adad','asdad');
        </script>
    </body>
</html>