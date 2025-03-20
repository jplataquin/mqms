@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="#">
                        <span>
                        Report
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Purchase
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

   

        <table>
            <tr>
                <th>Material Item</th>
            </tr>
            @foreach($purchase_order_items as $po_item)
            <tr>
                <td>
                    {{$po_item->MaterialItem->formatted_name}}
                </td>
            </tr>
            @endforeach
        </table>

    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection