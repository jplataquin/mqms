@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/budget">
                    <span>
                       Budget
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Component Items
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Component Item
        </div>
        <div class="folder-form-body">
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Query</label>
                        <input type="text" id="query" class="form-control"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container" id="list">
        <table class="w-100 table">
            <thead>
                <tr>
                    <th>
                        Item
                    </th>
                    <th>
                        Quantity
                    </th>
                    <th>
                        Price
                    </th>
                    <th>
                        Total
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($component_item_arr as $ci)
                <tr>
                    <td>
                        {{$ci->data->name}}
                    </td>
                    <td>
                        {{ number_format($ci->data->quantity,2) }} {{ $unit_options[$ci->data->unit_id]->text }}
                    </td>
                    <td>
                         {{ number_format($ci->data->budget_price,2) }}
                    </td>
                    <td>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

   

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';


</script>
</div>
@endsection