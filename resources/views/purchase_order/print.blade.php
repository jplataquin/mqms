<!DOCTYPE html>
    <head>
         <style>
            .text-left{
                text-align:left;
            }

            .bold{
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div>
            @foreach(['Supplier','Site','Accounting','Warehouse'] as $copy)
            <table style="border-collapse:collapse" border="1">
                <tr>
                    <td colspan="4">
                        <strong>Purchase Order</strong>
                    </td>
                </tr>
                <tr>
                    <td class="text-left bold">PO#</td>
                    <td>{{$purchase_order->id}}</td>
                    <td class="text-left bold">Supplier</td>
                    <td>{{$supplier->name}}</td>
                </tr>
            </table>
            @endforeach
        </div>
    </body>
</html>