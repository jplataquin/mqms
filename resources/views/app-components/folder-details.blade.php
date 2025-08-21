<div class="folder-form-container">
    <div class="folder-form-tab">
        {{$title}}
    </div>
    <div class="folder-form-body">
        <table class="record-table-horizontal" hx-boost="true" hx-select="#content" hx-target="#main">
            <tbody>
                @foreach($items as $label => $value)
                <tr>
                    <th width="150px">{{$label}}</th>
                    <td>{{$value}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>