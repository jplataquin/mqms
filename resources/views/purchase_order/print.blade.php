<!DOCTYPE html>
    <head>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    </head>
    <body>
        <div>
            @foreach(['Supplier','Site','Accounting','Warehouse'] as $copy)
            <table style="border:collapse" border="1">
                <tr>
                    <td>
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