<html>
    <head>
    </head>
    <body>
        <h1>TEst</h1>
        <table border="1">

            @foreach($contract_items as $contract_item)

                
                <tr>
                    <td>{{$contract_item->item_code}}</td>
                    <td>{{$contract_item->description}}</td>
                </tr>

                @php
                    $first = true;
                @endphp

                @foreach($contract_item->components as $component)
                    <tr>
                        @if($first)
                        <td rowspan="{{count($contract_item->components)}}">
                            {{$component->name}}
                        </td>
                            @php
                                $first = false;
                            @endphp
                        @endif
                        <td>
                            {{$component->name}}
                        </td>
                    </tr>    
                @endforeach
                
            @endforeach
            
        </table>
    </body>
</html>