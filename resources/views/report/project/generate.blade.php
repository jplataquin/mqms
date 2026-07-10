@extends('layouts.app')

@section('content')
<style>
    /* Modern variables and general layout resets */
    :root {
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-200: #e2e8f0;
        --slate-300: #cbd5e1;
        --slate-400: #94a3b8;
        --slate-600: #475569;
        --slate-700: #334155;
        --slate-800: #1e293b;
        --slate-900: #0f172a;
        
        --indigo-500: #6366f1;
        --indigo-600: #4f46e5;
        --blue-500: #3b82f6;
        --blue-600: #2563eb;
        --amber-500: #f59e0b;
        --amber-600: #d97706;
    }

    /* Table hierarchy styling - Sleek, Clean and Professional */
    .report-table {
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        margin-top: 1.5rem;
    }

    .report-table tr {
        transition: background-color 0.15s ease;
    }

    .report-table td, .report-table th {
        border: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 0.875rem 1.25rem !important;
        line-height: 1.5;
    }

    /* Contract Item Row Header */
    .contract_item {
        background-color: #1e293b !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 13.5px !important;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 1.25rem 1.25rem !important;
        vertical-align: middle !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .contract_item p {
        margin: 0 !important;
        font-family: 'Nunito', sans-serif;
    }

    /* Component Row Header */
    .component {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        padding: 1rem 1.25rem !important;
        vertical-align: middle !important;
        border-left: 5px solid #6366f1 !important; /* Indigo accent bar */
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .component a {
        color: #6366f1 !important;
        transition: color 0.15s;
    }
    .component a:hover {
        color: #4f46e5 !important;
    }

    /* Component Item Row Header */
    .component_item {
        background-color: #ffffff !important;
        color: #334155 !important;
        font-weight: 600 !important;
        font-size: 12.5px !important;
        padding: 0.875rem 1.25rem 0.875rem 2.25rem !important; /* Nested Indentation */
        vertical-align: middle !important;
        border-bottom: 1px solid #e2e8f0 !important;
        position: relative;
    }
    .component_item::before {
        content: '';
        position: absolute;
        left: 1.25rem;
        top: 0;
        bottom: 0;
        width: 3px;
        background-color: #cbd5e1;
    }

    /* Material Item Row Styling */
    .material_item {
        background-color: #fafafa !important;
        color: #475569 !important;
        font-size: 12px !important;
        padding: 0.75rem 1.25rem 0.75rem 3.5rem !important; /* Nested Indentation */
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    /* Progress and Sub-rows with Timeline Guides */
    .report-table td[style*="padding-left:5em"] {
        padding-left: 4.5rem !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        position: relative;
        background-color: #fafafa !important;
    }
    .report-table td[style*="padding-left:5em"]::before {
        content: '';
        position: absolute;
        left: 3.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e2e8f0; /* Timeline guidelines vertical connector */
    }
    .report-table td[style*="padding-top:1.8em"] {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        background-color: #fafafa !important;
    }

    /* Beautiful Progress Bars */
    .progress {
        height: 14px !important;
        border-radius: 10px !important;
        background-color: #e2e8f0 !important;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.1) !important;
        overflow: hidden !important;
        border: none !important;
        margin-top: 0.35rem;
    }
    .progress-bar {
        border-radius: 10px !important;
        font-weight: bold;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Custom CSS-based Stacked Progress Bars */
    .horizontal-bar-stacked {
        width: 100%;
        min-width: 100%;
        height: 100% !important;
        border-collapse: collapse;
        border-radius: 10px;
        overflow: hidden;
    }
    .horizontal-bar-stacked td {
        text-align: center;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        font-family: 'Nunito', sans-serif;
        text-shadow: 0 1px 2px rgba(0,0,0,0.4);
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0 !important;
        height: 100%;
        vertical-align: middle;
    }
    .horizontal-bar-stacked-expense {
        background: linear-gradient(90deg, #3b82f6, #1d4ed8) !important; /* Vivid Blue */
    }
    .horizontal-bar-stacked-overhead {
        background: linear-gradient(90deg, #fbbf24, #f59e0b) !important; /* Warm Amber */
    }
    .horizontal-bar-stacked-default {
        background-color: rgba(0, 0, 0, 0.08) !important;
        color: #64748b !important;
        text-shadow: none !important;
    }

    /* Styles inside Dark Folder Container */
    .folder-form-body .horizontal-bar-stacked-default {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: rgba(255, 255, 255, 0.4) !important;
    }

    /* Metadata Table Styling */
    .record-table-horizontal {
        background-color: rgba(255, 255, 255, 0.02) !important;
        border-radius: 12px !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        overflow: hidden !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        margin-bottom: 2rem !important;
    }
    .record-table-horizontal tr:hover {
        background-color: rgba(255, 255, 255, 0.04) !important;
        color: #ffffff !important;
    }
    .record-table-horizontal th {
        background-color: rgba(255, 255, 255, 0.04) !important;
        color: #cbd5e1 !important;
        font-weight: 700 !important;
        padding: 0.875rem 1.25rem !important;
        width: 220px !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .record-table-horizontal td {
        color: #ffffff !important;
        padding: 0.875rem 1.25rem !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .record-table-horizontal tr:last-child th,
    .record-table-horizontal tr:last-child td {
        border-bottom: none !important;
    }

    /* KPI Cards - SaaS inspired Dashboard Indicators */
    .kpi-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        border-left: 5px solid #6366f1 !important;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s, background-color 0.2s;
        text-align: left !important;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.05);
        box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.5);
    }
    .kpi-card-budget {
        border-left-color: #818cf8 !important; /* Indigo */
    }
    .kpi-card-expense {
        border-left-color: #38bdf8 !important; /* Sky Blue */
    }
    .kpi-card-overhead {
        border-left-color: #fbbf24 !important; /* Amber */
    }
    .kpi-title {
        font-size: 0.725rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8 !important;
        margin-bottom: 0.5rem;
    }
    .kpi-value {
        font-size: 1.625rem !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        margin-bottom: 0;
        font-family: 'Nunito', sans-serif;
    }

    /* Modern Alert Callout */
    #callout-danger {
        border: none !important;
        border-left: 5px solid #ef4444 !important;
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        border-radius: 10px;
        padding: 1.125rem 1.5rem !important;
        margin-bottom: 1.75rem !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08) !important;
    }
    #callout-danger h4 {
        color: #991b1b !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        margin: 0 !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    #callout-danger h4::before {
        content: "\F5C4"; /* Bootstrap icon bi-exclamation-triangle-fill */
        font-family: "bootstrap-icons";
        font-size: 1.15rem;
        color: #ef4444;
    }

    /* Premium styled print button */
    #printBtn {
        background-color: #fbbf24 !important;
        border: none !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 12.5px !important;
        border-radius: 8px !important;
        padding: 0.625rem 1.75rem !important;
        box-shadow: 0 4px 10px rgba(251, 191, 36, 0.25) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    #printBtn:hover {
        background-color: #f59e0b !important;
        transform: translateY(-1.5px) !important;
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35) !important;
    }

    /* Hover effects for table rows */
    .report-table tr:hover td {
        background-color: rgba(99, 102, 241, 0.015) !important;
    }
    .report-table tr:hover td.contract_item,
    .report-table tr:hover td.component {
        background-color: inherit !important; /* Prevent headers from changing hover bg */
    }

    /* Red indicator for over-budget items */
    .text-danger {
        color: #ef4444 !important;
        font-weight: 700 !important;
    }

    /* Sticky headers configuration on desktop */
    @media (min-width: 961px) {
        .contract_item {
            position: sticky !important;
            top: 40px;
            z-index: 10;
        }
        .component {
            position: sticky !important;
            top: 114px;
            z-index: 9;
        }
    }
</style>
<div id="content">
    <div class="container">

        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/roles">
                        <span>
                        Report
                        </span>                    
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Project
                        </span>                    
                        <i class="ms-2 bi bi-bar-chart"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>


        <div id="callout-danger" class="callout callout-danger d-none">
            <h4 id="callout-danger-p">Warning: Non-conforming items</h4> 
        </div>

        <div class="folder-form-container">
            <div class="folder-form-tab">
                Project Report
            </div>

            <div class="folder-form-body">
                <table class="record-table-horizontal mb-4">
                    <tbody>
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
                            <th>As of</th>
                            <td>{{$as_of_display}}</td>
                        </tr>
                    </tbody>
                </table> 


                <div class="row g-3 mb-4">
                    <div class="col-lg-4 col-md-12">
                        <div class="kpi-card kpi-card-budget shadow-sm">
                            <div class="kpi-title">Total Budget</div>
                            <div class="kpi-value" id="material_budget_grand_total" data-value="0">-</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="kpi-card kpi-card-expense shadow-sm">
                            <div class="kpi-title">Total Expense</div>
                            <div class="kpi-value check" id="material_expense_grand_total" data-check-target="#material_budget_grand_total" data-value="0">-</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="kpi-card kpi-card-overhead shadow-sm">
                            <div class="kpi-title">Total Overhead</div>
                            <div class="kpi-value" id="material_overhead_grand_total" data-value="0">-</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 row">
                    <div class="col-lg-12">
                    
                        <div class="progress">
                            <table cellpadding="0" cellspacing="0" class="horizontal-bar-stacked" id="material_expense_grand_total_percent">
                                <tr>
                                    <td class="horizontal-bar-stacked-expense"></td>
                                    <td class="horizontal-bar-stacked-overhead"></td>
                                    <td class="horizontal-bar-stacked-default"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-2 row">
                    <div class="col-lg-12 text-end">
                        <button class="btn btn-warning" id="printBtn" onclick="window.open('/report/project/print?project_id={{$project_id}}&section_id={{$section_id}}&contract_item_id={{$contract_item_id}}&component_id={{$component_id}}&as_of={{$as_of}}&material_items={{$material_items_request}}','_blank')">Print</button>
                    </div>
                </div>
            </div>
        </div>



        <table class="table report-table">
            @foreach($report as $contract_item_id => $contract_item)
                <tr>
                    <td class="contract_item">
                        <p>  
                             {{ $contract_item_arr[$contract_item_id]->item_code }} {{$contract_item_arr[$contract_item_id]->description}}
                        </p>
                        <div class="progress">
                            <table cellpadding="0" cellspacing="0" class="horizontal-bar-stacked contract_item_percent" data-id="{{$contract_item_id}}">
                                <tr>
                                    <td class="horizontal-bar-stacked-expense"></td>
                                    <td class="horizontal-bar-stacked-overhead"></td>
                                    <td class="horizontal-bar-stacked-default"></td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td class="contract_item text-end">
                        <div class="fw-bold text-end mb-0 contract_item_material_budget_total" data-id="{{$contract_item_id}}" data-value="0">
                            (MB) P 0.00
                        </div>
                        <div class="fw-bold contract_item_material_expense_total text-end check" data-check-target=".contract_item_material_budget_total[data-id='{{$contract_item_id}}']" data-id="{{$contract_item_id}}" data-value="0">
                            (ME) P 0.00
                        </div>
                        <div class="fw-bold contract_item_material_overhead_total text-end" data-id="{{$contract_item_id}}" data-value="0">
                            (MO) P 0.00
                        </div>       
                    </td>
                </tr>
                    
                
                @foreach($contract_item as $component_id => $component)
                    
                    <tr>
                        <td style="padding-left:1em" class="component fw-bold">
                            
                            {{ $component_arr[ $component_id ]->name }}
                            
                            <a class="ms-3 link-offset-2 link-underline link-underline-opacity-0" href="/project/section/contract_item/component/{{$component_id}}?b={{ urlencode($url) }}" hx-boost="true" hx-select="#content" hx-target="#main">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>

                        <td class="text-end component">
                            
                            <div class="fw-bold component_material_budget_total" data-contract_item_id="{{$contract_item_id}}" data-id="{{$component_id}}" data-value="0"> - </div>

                            <div class="fw-bold component_material_expense_total check" data-check-target=".component_material_budget_total[data-id='{{$component_id}}']" data-contract_item_id="{{$contract_item_id}}" data-id="{{$component_id}}" data-value="0" > - </div> 

                            <div class="fw-bold component_material_overhead_total" data-value="{{$total_po_overhead_arr[$component_id]}}" data-id="{{$component_id}}" data-contract_item_id="{{$contract_item_id}}">
                                (MO) P {{ number_format($total_po_overhead_arr[$component_id],2) }}
                            </div>
                        </td>
                    </tr>

                    @foreach($component as $component_item_id => $component_item)
                        <tr>
                            @php 
                                $component_item_material_budget = $component_item_arr[$component_item_id]->quantity * $component_item_arr[$component_item_id]->budget_price;
                            @endphp
                            <th style="padding-left:2em" class="component_item" data-value="{{$component_item_material_budget}}">{{$component_item_arr[$component_item_id]->name}}</th>
                            <td class="text-end component_item">
                                
                                <div class="fw-bold component_item_material_budget_total" data-id="{{$component_item_id}}" data-component_id="{{$component_id}}" data-value="{{$component_item_material_budget}}">
                                    (MB) P {{ number_format($component_item_material_budget,2) }}
                                </div>
                                <div 
                                    class="fw-bold component_item_material_expense_total check" 
                                    data-check-target=".component_item_material_budget_total[data-id='{{$component_item_id}}']" 
                                    data-component_id="{{$component_id}}" 
                                    data-id="{{$component_item_id}}" 
                                    data-value="0"
                                > 
                                    - 
                                    
                                </div>
                            </td>
                        </tr>

                        @foreach($component_item as $material_quantity_id => $result)

                            
                            @php 
                                $material_item = $material_item_arr[ $material_quantity_arr[$material_quantity_id]->material_item_id ];
                            @endphp

                            <tr>

                                <td style="padding-left:3em" class="material_item">{{ $material_item->formatted_name }}</td>
                                <td class="component_item_material_expense text-end material_item" data-component_item_id="{{$component_item_id}}" data-value="{{$result['po_amount']}}">
                                    
                                    (ME) P {{ number_format($result['po_amount'],2) }}
                                    <br>
                                    <span data-value="{{$result['budget_quantity']}}" class="budget_quantity" data-id="{{$material_quantity_id}}">{{ number_format($result['budget_quantity'],2) }} Qty</span>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left:5em">
                                    @php 
                                        if($result['request_quantity'] && $result['budget_quantity'] != 0){
                                            $request_percentage = ($result['request_quantity'] / $result['budget_quantity']) * 100;
                                            $request_percentage = round($request_percentage,2);
                                        }else{
                                            $request_percentage = 0;
                                        }
                                    @endphp
                                    
                                    Request

                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{$request_percentage}}%;" aria-valuenow="{{$request_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$request_percentage}}%</div>
                                    </div>
                                    
                                </td>
                                <td style="padding-top:1.8em" class="text-end">
                                    <span data-value="{{$result['request_quantity']}}" class="request_quantity check" data-check-level="1" data-check-target=".budget_quantity[data-id='{{$material_quantity_id}}']" data-id="{{$material_quantity_id}}">{{ number_format($result['request_quantity'],2) }} Qty</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left:5em">
                                    @php 
                                        if($result['po_quantity'] && $result['budget_quantity'] != 0){
                                            $po_percentage = ($result['po_quantity'] / $result['budget_quantity']) * 100;
                                            $po_percentage = round($po_percentage,2);
                                        }else{
                                            $po_percentage = 0;
                                        }
                                    @endphp
                                    
                                    PO

                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{$po_percentage}}%;" aria-valuenow="{{$po_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$po_percentage}}%</div>
                                    </div>
                                    
                                </td>
                                <td style="padding-top:1.8em" class="check text-end" data-value="{{$result['po_quantity']}}" data-check-level="1" data-check-target=".request_quantity[data-id='{{$material_quantity_id}}']">
                                    {{ number_format($result['po_quantity'],2) }} Qty
                                </td>
                            </tr>
                        @endforeach

                    @endforeach

                @endforeach

            @endforeach
        </table>
    </div>

    <script type="module">
        import {$q} from '/adarna.js';
        
        let overbudget_count = 0;

        function total_component_item_material_expense(){

            $q('.component_item_material_expense_total').apply(elem=>{
                let component_item_id = elem.getAttribute('data-id');
                let total = 0;
               
                $q('.component_item_material_expense[data-component_item_id="'+component_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(ME) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_component_material_expense(){

            $q('.component_material_expense_total').apply(elem=>{
                let component_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_item_material_expense_total[data-component_id="'+component_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(ME) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_component_material_budget(){
            $q('.component_material_budget_total').apply(elem=>{
                let component_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_item_material_budget_total[data-component_id="'+component_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(MB) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_contract_item_material_expense(){
            $q('.contract_item_material_expense_total').apply(elem=>{
                let contract_item_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_material_expense_total[data-contract_item_id="'+contract_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(ME) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_contract_item_material_budget(){
            $q('.contract_item_material_budget_total').apply(elem=>{
                let contract_item_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_material_budget_total[data-contract_item_id="'+contract_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(MB) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_contract_item_material_overhead(){
            
            $q('.contract_item_material_overhead_total').apply(elem=>{
                let contract_item_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_material_overhead_total[data-contract_item_id="'+contract_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(MO) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function grand_total_material_expense(){

            let elem  = $q('#material_expense_grand_total').first();
            let total = 0;
            
                $q('.contract_item_material_expense_total').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = 'P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            
        }

        function grand_total_material_budget(){

            let elem  = $q('#material_budget_grand_total').first();
            let total = 0;

            $q('.contract_item_material_budget_total').apply(el=>{
                
                let value = parseFloat( el.getAttribute('data-value') );
                
                if(isNaN(value)){
                    value = 0;
                }

                total = total + value;
            });

            elem.innerText = 'P '+window.util.numberFormat(total);
            elem.setAttribute('data-value',total);

        }

        function grand_total_material_overhead(){

            let elem  = $q('#material_overhead_grand_total').first();
            let total = 0;

            $q('.contract_item_material_overhead_total').apply(el=>{
                
                let value = parseFloat( el.getAttribute('data-value') );
                
                if(isNaN(value)){
                    value = 0;
                }

                total = total + value;
            });

            elem.innerText = 'P '+window.util.numberFormat(total);
            elem.setAttribute('data-value',total);

        }

        function contract_item_percentage(){

            $q('.contract_item_percent').apply(elem=>{

                let contract_item_id    = elem.getAttribute('data-id');


                let total_budget        = parseFloat( $q('.contract_item_material_budget_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );
                let total_expense       = parseFloat( $q('.contract_item_material_expense_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );
                let total_overhead      = parseFloat( $q('.contract_item_material_overhead_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );
                
                //Skip if total budget is zero
                if(total_budget <= 0) return false;

                let expense_percentage  = (total_expense / total_budget) * 100;
                expense_percentage      = Math.round(expense_percentage);

                let overhead_percentage = (total_overhead / total_budget) * 100;
                overhead_percentage     = Math.round(overhead_percentage);

                let default_percentage  = 100 - (expense_percentage + overhead_percentage);
                let total_percentage    = expense_percentage + overhead_percentage + default_percentage;

                let expense_td  = elem.querySelector('.horizontal-bar-stacked-expense');
                let overhead_td = elem.querySelector('.horizontal-bar-stacked-overhead');
                let default_td  = elem.querySelector('.horizontal-bar-stacked-default');

                if(total_percentage > 100){
                    expense_percentage          = 50;
                    overhead_percentage         = 50;
                    default_percentage          = 0;
                }

                if(expense_percentage){
                    expense_td.style.width      = expense_percentage+'%';
                    expense_td.style.minWidth   = expense_percentage+'%';
                    expense_td.innerText        = expense_percentage+'%';
                }

                if(overhead_percentage){
                    overhead_td.style.width      = overhead_percentage+'%';
                    overhead_td.style.minWidth   = overhead_percentage+'%';
                    overhead_td.innerText        = overhead_percentage+'%';
                }

                if(default_percentage){
                    default_td.style.width      = default_percentage+'%';
                    default_td.style.minWidth   = default_percentage+'%';
                    default_td.innerText        = default_percentage+'%';
                }

                if(default_percentage <= 0){
                    default_td.style.display = 'none';
                }

                if(overhead_percentage <= 0){
                    overhead_td.style.display = 'none';
                }

                if(expense_percentage <= 0){
                    expense_td.style.display = 'none';
                }
            });
        }

        function grand_total_material_percentage(){

            let elem                            = $q('#material_expense_grand_total_percent').first();
            let material_expense_grand_total    = parseFloat( $q('#material_expense_grand_total').first().getAttribute('data-value') );
            let material_budget_grand_total     = parseFloat( $q('#material_budget_grand_total').first().getAttribute('data-value') );
            let material_overhead_grand_total   = parseFloat( $q('#material_overhead_grand_total').first().getAttribute('data-value') );

            if(material_budget_grand_total <= 0 ) return false;

            let expense_percentage  = (material_expense_grand_total / material_budget_grand_total) * 100;
            expense_percentage      = Math.round(expense_percentage);

            let overhead_percentage  = (material_overhead_grand_total / material_budget_grand_total) * 100;
            overhead_percentage      = Math.round(overhead_percentage);

            let default_percentage  = 100 - (expense_percentage + overhead_percentage);
            let total_percentage    = expense_percentage + overhead_percentage + default_percentage;

            let expense_td  = elem.querySelector('.horizontal-bar-stacked-expense');
            let overhead_td = elem.querySelector('.horizontal-bar-stacked-overhead');
            let default_td  = elem.querySelector('.horizontal-bar-stacked-default');

            if(total_percentage > 100){
                expense_percentage          = 50;
                overhead_percentage         = 50;
                default_percentage          = 0;
            }

            if(expense_percentage){
                expense_td.style.width      = expense_percentage+'%';
                expense_td.style.minWidth   = expense_percentage+'%';
                expense_td.innerText        = expense_percentage+'%';
            }

            if(overhead_percentage){
                overhead_td.style.width      = overhead_percentage+'%';
                overhead_td.style.minWidth   = overhead_percentage+'%';
                overhead_td.innerText        = overhead_percentage+'%';
            }

            if(default_percentage){
                default_td.style.width      = default_percentage+'%';
                default_td.style.minWidth   = default_percentage+'%';
                default_td.innerText        = default_percentage+'%';
            }

            if(default_percentage <= 0){
                default_td.style.display = 'none';
            }

            if(overhead_percentage <= 0){
                overhead_td.style.display = 'none';
            }

            if(expense_percentage <= 0){
                expense_td.style.display = 'none';
            }
        }

        function check(){

            let nonconforming_item_count = 0;

            $q('.check').apply(elem=>{

                elem.classList.remove('text-danger');

                let target_query = elem.getAttribute('data-check-target');
                let value  = parseFloat( elem.getAttribute('data-value') );
                
                if(isNaN(value)){
                    value = 0;
                }

                if(!target_query) return false;

                let target = $q(target_query).first();

                if(!target) return false;

                let target_value = parseFloat( target.getAttribute('data-value') );

                if(isNaN(target_value)){
                    target_value = 0;
                }
                
                if(value > target_value){
                    elem.classList.add('text-danger');
                    nonconforming_item_count++;
                }
            });


            let material_budget_grand_total_el      = $q('#material_budget_grand_total').first();
            let material_expene_grand_total_el      = $q('#material_expense_grand_total').first();
            let materiaL_overhead_grand_total_el    = $q('#material_overhead_grand_total').first();

            let material_budget_grand_total     = parseFloat( material_budget_grand_total_el.getAttribute('data-value') );
            let material_expense_grand_total    = parseFloat( material_expene_grand_total_el.getAttribute('data-value') );
            let material_overhead_grand_total   = parseFloat( materiaL_overhead_grand_total_el.getAttribute('data-value') );

            if(isNaN(material_expense_grand_total)){
                material_expense_grand_total = 0;
            }

            if(isNaN(material_budget_grand_total)){
                material_budget_grand_total = 0;
            }

            if(isNaN(material_budget_grand_total)){
                material_overhead_grand_total = 0;
            }

            let total_expense_and_overhead = material_expense_grand_total + material_overhead_grand_total;

            if(total_expense_and_overhead > material_budget_grand_total){
                material_expene_grand_total_el.classList.add('text-danger');
                materiaL_overhead_grand_total_el.classList.add('text-danger');
                nonconforming_item_count++;
            }

            if(nonconforming_item_count){
                $q('#callout-danger').first().classList.remove('d-none');  
                $q('#callout-danger-p').first().innerHTML = 'Warning: '+nonconforming_item_count+' Non-conforming items detected';  
            }
        }

        /** Note the function call must run in order **/

        total_component_item_material_expense();

        total_component_material_expense();
        
        total_component_material_budget();
        
        total_contract_item_material_expense();
        
        total_contract_item_material_budget();

        total_contract_item_material_overhead();

        grand_total_material_expense();

        grand_total_material_budget();

        grand_total_material_overhead();

        contract_item_percentage();

        grand_total_material_percentage();

        check();
    </script>
</div>
@endsection