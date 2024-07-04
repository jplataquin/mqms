@extends('layouts.app')

@section('content')
<div class="container">
<h5>Material Quantity Request » Component » Unavailable</h5>
<hr>

    <table class="table bordered">
        <tbody>
            <tr>
                <th>Project / Section</th>
                <td>
                    {{$project->name}} » {{$section->name}}
                </td>
                <td>
                    {{$project->status}}
                </td>
            </tr>
            <tr>
                <th>Component</th>
                <td>
                    {{$component->name}}
                </td>
                <td>
                    {{$component->status}}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="text-center">
        <h5>*** Material request is not available for these parameters, because the Project and/or Component status is not yet "approved" or "active" ***</h5>
        <br>
        <a href="/material_quantity_request/select/create">Click here to return to previous page</a>
    </div>

</div>
@endsection