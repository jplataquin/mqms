<style>
    table{
        border-collapse:collapse;
    }

    td{
        padding: 3px;
    }
    
    th{
        padding:3px;
    }
    
    .mb-5{
        margin-bottom: 5px;
    }

    .ps-5{
        padding-left: 5px;
    }


    .border{
        border-left: solid 1px #000000;
    }
</style>
<page>

    <table border="1" class="mb-5">
        <tr>
            <th style="width:10%">Project</th>
            <td style="width:40%">{{$project_name}}</td>
            <th style="width:10%">Section</th>
            <td style="width:40%">{{$section_name}}</td>
        </tr>
        <tr>
            <th>Contract Item</th>
            <td>{{$contract_item_name}}</td>
            <th>Component</th>
            <td>{{$component_name}}</td>
        </tr>
        <tr>
            <th>Date Scope</th>
            <td colspan="3">{{$from}} - {{$to}}</td>
        </tr>
        <tr>
            <th>Material Group</th>
            <td colspan="3">{{$material_group->name}}</td>
        </tr>
    </table>


    @foreach($result as $material_item_id => $res1)

            <div class="mb-5 ps-5 border">
                <h3>{{ $material_item_options[$material_item_id]->text }}</h3>

                @foreach($res1 as $supplier_id => $res2)
                    <div class="mb-5 ps-5 border">
                        <h4>{{ $supplier_options[$supplier_id]->text }}</h4>

                        @foreach($res2 as $payment_term_id => $res3)
                            <div class="mb-5 ps-5">
                                <h5>{{ $payment_term_options[$payment_term_id]->text }}</h5>

                                <ul>
                                    @foreach($res3 as $price => $created_at)
                                        <li>P{{number_format($price,2)}} [ {{$created_at}} ]</li>
                                    @endforeach
                                
                                </ul>
                            </div>
                        @endforeach
                    </div>

                @endforeach

            </div>

        @endforeach


    <page_footer>
        <br>
        <table>
            <tr>
                <td style="width: 50%;font-size:12px;">
                    <strong>Price Report:</strong> {{$material_group->name}} - {{$current_datetime}}
                </td>
                <td style="width: 50%; text-align: right;font-size:12px">
                    [[page_cu]] / [[page_nb]]
                </td>
            </tr>
        </table>
    </page_footer>
</page>