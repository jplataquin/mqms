@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="#">
                        <span>
                        Report
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Fulfilment
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

         <table>
            <tr>
                <td>
                    {{ number_format($request_count,2) }}
                </td>
                <td>
                    {{ number_format($target_hit,2) }}
                </td>
                <td>
                    {{ number_format($target_missed,2) }}
                </td>
            </tr>
        </table>   

    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection