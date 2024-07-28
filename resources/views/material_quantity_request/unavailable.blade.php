@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">

<div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
                    <span>
                        Request
                    </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span>
                       Material Quantity
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Unavailable
                    </span>		
                </a>
            </li>
        </ul>
    </div>
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
        <br>
        <h5>*** Material request is not available for these parameters, because the Project and/or Component status is not yet "approved" or "active" ***</h5>
        <br>
        <a href="/material_quantity_request/select/create">Click here to return to previous page</a>
    </div>

</div>
</div>
@endsection