<page>
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
</page>