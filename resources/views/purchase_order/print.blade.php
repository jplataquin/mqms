<!DOCTYPE html>
    <head>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    </head>
    <body>
        <div>
            @foreach(['Supplier','Site','Accounting','Warehouse'] as $copy)
            <table border="1">
                <tr>
                    <td>
                        <strong>Purchase Order</strong>
                    </td>
                </tr>
            </table>
            @endforeach
        </div>
    </body>
</html>