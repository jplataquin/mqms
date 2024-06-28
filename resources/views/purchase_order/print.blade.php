<!DOCTYPE html>
    <head>
         <style>
            .text-left{
                text-align: left;
            }

            .bold{
                font-weight: bold;
            }

            .table{
                border-collapse:collapse;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: 5px;
            }

            .text-center{
                text-align: center;
            }

            td{
                padding: 3px;
            }

        </style>
    </head>
    <body>
        <div>
            @foreach(['Supplier','Site','Accounting','Warehouse'] as $copy)
            <table class="table" style="width:700px" border="1">
                <tr>
                    <td colspan="4" class="text-center bold">
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
            </table>
            @endforeach
        </div>
    </body>
</html>