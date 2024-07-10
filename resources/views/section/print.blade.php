<html>
    <head>
    </head>
    <body>
        <h1>TEst</h1>
        <table border="1">

            @foreach($components as $component)
                @foreach($component->ComponentItems as $item)
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