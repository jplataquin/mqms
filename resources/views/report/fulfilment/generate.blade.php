@extends('layouts.app')

@section('content')
@php
    $theme_color = $percentage >= 90 ? '#198754' : '#dc3545'; // Green if >=90%, else Red
    $status_label = $percentage >= 90 ? 'Target Met' : 'Needs Attention';
    $status_badge_class = $percentage >= 90 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
    $status_text_class = $percentage >= 90 ? 'text-success' : 'text-danger';
    
    // Extract clean dates for parameters/queries
    $from_date = request('from') ?? explode(' ', $from)[0];
    $to_date = request('to') ?? explode(' ', $to)[0];
@endphp

<style>
    .kpi-container {
        font-family: 'Zen Kaku Gothic New', sans-serif;
    }
    
    .kpi-header {
        background: var(--bs-body-bg);
        border-bottom: 1px solid var(--bs-border-color);
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        background-color: var(--bs-secondary-bg);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 12px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .kpi-card.primary::before { background-color: var(--bs-primary); }
    .kpi-card.success::before { background-color: var(--bs-success); }
    .kpi-card.danger::before { background-color: var(--bs-danger); }
    .kpi-card.info::before { background-color: var(--bs-info); }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    .metric-label {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--bs-secondary-color);
    }

    .metric-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* Circular Progress Ring Gauge */
    .gauge-wrapper {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }

    .gauge-bg {
        fill: none;
        stroke: var(--bs-border-color-translucent);
        stroke-width: 12;
    }

    .gauge-progress {
        fill: none;
        stroke-width: 12;
        stroke-linecap: round;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
        transition: stroke-dashoffset 0.8s ease-in-out;
    }

    .gauge-center-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        width: 100%;
    }

    .gauge-percentage {
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .gauge-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
        margin-top: 4px;
    }

    .card-title-pill {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.35em 0.85em;
        border-radius: 20px;
    }

    .info-list dt {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--bs-secondary-color);
    }

    .info-list dd {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .clickable-row-link {
        transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
    }
    .clickable-row-link:hover {
        background-color: rgba(13, 110, 253, 0.08) !important; /* light blue highlight */
        color: var(--bs-primary) !important;
    }
</style>

<div id="content" class="kpi-container">
    <div class="container py-4">
        <!-- Breadcrumbs Section -->
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="/report/fulfilment/parameters">
                        <span>Report</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>Request to Purchase Timeframe KPI</span>
                    </a>
                </li>
            </ul>
        </div>
        <hr class="my-4">

        <!-- Top Header Action Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold text-body">Request to Purchase Timeframe KPI</h1>
                <p class="text-secondary mb-0">Analyzes and measures fulfillment efficiency against organizational targets.</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                @if($percentage < 90)
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="include_misses_check" checked>
                    <label class="form-check-label small fw-semibold text-secondary" for="include_misses_check">
                        Include Misses Breakdown in Print
                    </label>
                </div>
                @endif
                <a id="print_report_link" href="/report/fulfilment/print?from={{ $from_date }}&to={{ $to_date }}&include_misses=1" class="btn btn-warning d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold">
                    <i class="bi bi-printer-fill"></i>
                    <span>Print Report</span>
                </a>
                <a href="/report/fulfilment/parameters" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Change Parameters</span>
                </a>
            </div>
        </div>

        <!-- Scope and Status Summary Card -->
        <div class="card mb-4 border-0 shadow-sm bg-body-tertiary">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-3">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div>
                        <span class="text-uppercase text-secondary fw-semibold small d-block">Analysis Period</span>
                        <span class="fs-5 fw-bold">{{ Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ Carbon\Carbon::parse($to)->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-secondary">Status:</span>
                    <span class="badge {{ $status_badge_class }} px-3 py-2 rounded-pill fw-bold text-uppercase d-inline-flex align-items-center gap-1">
                        <i class="bi {{ $percentage >= 90 ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                        {{ $status_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Metric Cards Grid -->
        <div class="row g-4 mb-4">
            <!-- Metric Card 1: Total Requests -->
            <div class="col-12 col-md-4">
                <div class="card kpi-card primary h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="metric-value mb-1 text-body">{{ number_format($request_count) }}</h3>
                                <span class="metric-label">Total Requests</span>
                            </div>
                            <div class="metric-icon-wrapper bg-primary-subtle text-primary">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>
                        </div>
                        <p class="card-text text-secondary small mb-0">Total approved Material Quantity Requests (MQR) within scope.</p>
                    </div>
                </div>
            </div>

            <!-- Metric Card 2: Hits -->
            <div class="col-12 col-md-4">
                <div class="card kpi-card success h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="metric-value mb-1 text-success">{{ number_format($target_hit) }}</h3>
                                <span class="metric-label">Target Hits</span>
                            </div>
                            <div class="metric-icon-wrapper bg-success-subtle text-success">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        </div>
                        <p class="card-text text-secondary small mb-0">Requests fulfilled with an approved Purchase Order within {{ $threshold }} days.</p>
                    </div>
                </div>
            </div>

            <!-- Metric Card 3: Misses -->
            <div class="col-12 col-md-4">
                <div class="card kpi-card danger h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="metric-value mb-1 text-danger">{{ number_format($target_missed) }}</h3>
                                <span class="metric-label">Target Misses</span>
                            </div>
                            <div class="metric-icon-wrapper bg-danger-subtle text-danger">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                        </div>
                        <p class="card-text text-secondary small mb-0">Requests where Purchase Order approval exceeded the {{ $threshold }} day limit.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Visual Gauge and Info breakdown -->
        <div class="row g-4 mb-4">
            <!-- Circular Progress Column -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm bg-body-tertiary h-100">
                    <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="h5 fw-bold m-0 text-body">KPI Completion</h4>
                        <span class="badge {{ $status_badge_class }} card-title-pill">Target: 90%</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                        <div class="gauge-wrapper my-3">
                            <svg width="200" height="200" viewBox="0 0 200 200">
                                <circle class="gauge-bg" cx="100" cy="100" r="85"></circle>
                                <circle class="gauge-progress" cx="100" cy="100" r="85"
                                        stroke="{{ $theme_color }}"
                                        stroke-dasharray="534"
                                        stroke-dashoffset="{{ 534 - (534 * $percentage / 100) }}"></circle>
                            </svg>
                            <div class="gauge-center-text">
                                <div class="gauge-percentage">{{ $percentage }}%</div>
                                <div class="gauge-label">Fulfillment Rate</div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <h5 class="fw-bold mb-1 {{ $status_text_class }}">{{ $status_label }}</h5>
                            <p class="text-secondary small mb-0">
                                @if($percentage >= 90)
                                    Performance is healthy and meeting organizational expectations.
                                @else
                                    Fulfillment rate is currently below the 90.0% standard.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Context / policy explanation card -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm bg-body-tertiary h-100">
                    <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                        <h4 class="h5 fw-bold m-0 text-body">KPI Specification & Calculation</h4>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-secondary">
                            This metric measures the time elapsed from the formal approval of a **Material Quantity Request (MQR)** to the approval of its corresponding **Purchase Order (PO)**.
                        </p>
                        <hr class="my-3">
                        <dl class="info-list row g-2">
                            <dt class="col-sm-4">Target Standard</dt>
                            <dd class="col-sm-8 text-body">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ $threshold }} Days or Less</span>
                            </dd>

                            <dt class="col-sm-4">Target Threshold</dt>
                            <dd class="col-sm-8 text-body">90.0% of all approved requests</dd>

                            <dt class="col-sm-4">Current Score</dt>
                            <dd class="col-sm-8 text-body {{ $status_text_class }}">{{ $percentage }}%</dd>

                            <dt class="col-sm-4">Interpretation</dt>
                            <dd class="col-sm-8 text-secondary small">
                                A high fulfillment rate indicates standard purchasing operations. Low rates highlight potential bottlenecks in sourcing, vendor communication, or internal authorization workflows.
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($missed_entries) && $percentage < 90)
        <!-- Missed Entries Breakdown -->
        <div class="card border-0 shadow-sm bg-body-tertiary mb-4">
            <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                <h4 class="h5 fw-bold m-0 text-body">Target Misses Breakdown</h4>
                <p class="text-secondary small mb-0">Detailed list of approved Material Quantity Requests (MQR) that exceeded the {{ $threshold }}-day fulfillment threshold, along with their associated Purchase Orders (PO).</p>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50%;">Entity / ID</th>
                                <th style="width: 25%;">Type</th>
                                <th style="width: 25%;">Status / Info</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($missed_entries as $mqr_id => $po_ids)
                                <tr class="table-secondary">
                                    <td class="p-0" colspan="3">
                                        <a href="/material_quantity_request/{{ $mqr_id }}" class="d-block w-100 h-100 fw-bold text-decoration-none text-dark clickable-row-link" target="_blank" style="padding: 0.5rem 0.75rem;">
                                            <i class="bi bi-file-earmark-text-fill me-1"></i>
                                            Material Quantity Request #{{ $mqr_id }}
                                        </a>
                                    </td>
                                </tr>
                                @if(!empty($po_ids))
                                    @foreach($po_ids as $po)
                                        <tr>
                                            <td style="padding: 0;">
                                                <a href="/purchase_order/{{ $po['id'] }}" class="d-block w-100 h-100 text-decoration-none text-body clickable-row-link" target="_blank" style="padding: 0.5rem 0.75rem 0.5rem 2.5rem;">
                                                    <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                                    Purchase Order #{{ $po['id'] }}
                                                </a>
                                            </td>
                                            <td class="align-middle"><span class="badge bg-danger-subtle text-danger">Target Missed</span></td>
                                            <td class="text-secondary small align-middle">Exceeded target threshold (Took {{ $po['days'] }} days)</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td style="padding-left: 2.5rem;" colspan="3" class="text-muted fst-italic">
                                            <i class="bi bi-info-circle me-1"></i> No associated POs recorded as missed
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

        // Dynamically update print report link query param when include misses checkbox state changes
        const includeMissesCheck = document.getElementById('include_misses_check');
        const printReportLink = document.getElementById('print_report_link');
        if (includeMissesCheck && printReportLink) {
            includeMissesCheck.addEventListener('change', function() {
                let url = new URL(printReportLink.href, window.location.origin);
                if (this.checked) {
                    url.searchParams.set('include_misses', '1');
                } else {
                    url.searchParams.set('include_misses', '0');
                }
                printReportLink.href = url.pathname + url.search;
            });
        }
    </script>
</div>
@endsection
