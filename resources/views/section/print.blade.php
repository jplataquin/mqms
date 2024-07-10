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
                    $first = true;
                @endphp
                
                
                @foreach($component_items as $item)
                    @if($first == true)
                        <tr>
                            <td colspan="2">
                                {{$component->description}}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        @if($first == true)
                        <td rowspan="{{$component_count}}">
                            {{$component->name}}
                        </td>
                            @php
                                $first = false;
                            @endphp
                        @endif
                        <td>
                            {{$item->name}}
                        </td>
                    </tr>    
                @endforeach
                
            @endforeach
            
        </table>
    </body>
</html>