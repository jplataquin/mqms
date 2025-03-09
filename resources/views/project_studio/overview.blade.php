<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>

        table, tr, td, th {
            border: solid 1px #000000;
            border-collapse: collapse;
        }

    </style>
</head>
<body>
    
    <table>
        @foreach($data as $contract_item_id => $row_1)
            <tr>
                <td>{{$row_1->contract_item->item_code}}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>