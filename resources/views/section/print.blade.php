<html>
    <head>
    </head>
    <body>
        <h1>TEst</h1>
        <table border="1">

            @foreach($components as $component)

                @php
                    $component_items = $component->ComponentItems;
                    $component_count = count($component_items);
                @endphp
                <tr>
                    <td rowspan="{{$component_count}}">
                        {{$component->name}}
                    </td>
                    <td>
</td>
                </tr>
                
                @foreach($component_items as $item)
                   
                    <tr>
                        <td>
                            {{$item->name}}
                        </td>
                    </tr>    
                @endforeach
                
            @endforeach
            
        </table>
    </body>
</html>