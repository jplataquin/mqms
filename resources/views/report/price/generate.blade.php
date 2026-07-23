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
                       Material Item
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container mb-3">
            <div class="folder-form-tab">Price Report</div>
            <div class="folder-form-body">
                <table class="record-table-horizontal">
                    <tr>
                        <th>Project</th>
                        <td>{{$project_name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{$section_name}}</td>
                    </tr>
                    <tr>
                        <th>Contract Item</th>
                        <td>{{$contract_item_name}}</td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>{{$component_name}}</td>
                    </tr>
                    <tr>
                        <th>Date Scope</th>
                        <td>{{$from}} - {{$to}}</td>
                    </tr>
                    <tr>
                        <th>Material Group</th>
                        <td>{{$material_group->name}}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-4 border-info">
            <div class="card-body">
                <h5 class="card-title text-info mb-1">Overall Average Price</h5>
                <h2 class="card-text text-dark font-weight-bold" id="overall-average-price">P 0.00</h2>
                <small class="text-muted" id="overall-average-entries">Based on 0 price entries</small>
            </div>
        </div>

        @foreach($result as $material_item_id => $res1)
            <div class="mb-5">
                <h3 class="mb-3">{{ $material_item_options[$material_item_id]->text }}</h3>
                <table class="table w-100 table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Payment Term</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($res1 as $supplier_id => $res2)
                            @foreach($res2 as $payment_term_id => $res3)
                                @foreach($res3 as $price => $created_at)
                                    <tr>
                                        <td>{{ $supplier_options[$supplier_id]->text }}</td>
                                        <td>{{ $payment_term_options[$payment_term_id]->text }}</td>
                                        <td class="text-end price-cell" data-price="{{ $price }}">P {{ number_format($price, 2) }}</td>
                                        <td class="text-center">{{ $created_at }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    

    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

        // Calculate Overall Average Price
        document.addEventListener('DOMContentLoaded', () => {
            const priceCells = document.querySelectorAll('.price-cell');
            let total = 0;
            let count = 0;

            priceCells.forEach(cell => {
                const val = parseFloat(cell.getAttribute('data-price'));
                if (!isNaN(val)) {
                    total += val;
                    count++;
                }
            });

            const avg = count > 0 ? (total / count) : 0;
            const avgElement = document.getElementById('overall-average-price');
            const entriesElement = document.getElementById('overall-average-entries');

            if (avgElement) {
                avgElement.textContent = 'P ' + avg.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            if (entriesElement) {
                entriesElement.textContent = `Based on ${count} price ${count === 1 ? 'entry' : 'entries'}`;
            }
        });
    </script>
</div>
@endsection