<div class="folder-form-container">
    <div class="folder-form-tab">
        {{$title}}
    </div>
    <div class="folder-form-body">
        <table class="record-table-horizontal" hx-boost="true" hx-select="#content" hx-target="#main">
            <tbody>
                @foreach($items as $label => $value)
                <tr>
                    <th width="150px">{{ucwords($label)}}</th>

                    @if(isset($value['text']))

                        <td>
                            @if($value['href'])
                                <a href="{{$value['href']}}">{{ $value['text'] }}</a>
                            @else
                                {{$value['text']}}
                            @endif
                        </td>

                    @else

                        <td>{{$value}}</td>

                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>