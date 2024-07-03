@extends('layouts.app')

@section('content')
<div class="container">
<h5>Reports » A » Generate</h5>
<hr>

<table class="table bordered">
    <tbody>
        <tr>
            <th>Project</th>
            <td>{{$project->name}}</td>
        </tr>
        <tr>
            <th>Section</th>
            <td>{{$section->name}}</td>
        </tr>
        <tr>
            <th>Component</th>
            <td>{{$component->name}}</td>
        </tr>
        <tr>
            <th>Date Scope</th>
            <td></td>
        </tr>
    </tbody>
</table>

<hr>

    @foreach($component_items as $component_item)
    <table class="table bordered w-100">
        <tbody>
            <tr>
                <td colspan="3">{{$component_item->name}}</td>
            </tr>
            <tr>
                <th>Budget</th>
                <th>Requested</th>
                <th>PO</th>
            </tr>
            <tr>
                <td class="text-center">
                    {{$component_item->quantity}} {{$component_item->unit}} 
                </td>
                <td>
                </td>
                <td>
                </td>
            </tr>
        </tbody>
    </table>

    @endforeach

</div>

<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

</script>
@endsection
