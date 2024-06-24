@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    <ul>

                        <li>
                            Review
                            <ul>
                                <li>
                                    <a href="/review/material_quantity_requests">Material Quantity Request</a>  ({{ $materialQuantityRequestPendCount }})
                                </li>
                                <li>
                                    <a href="/review/material_canvass">Material Canvass</a> ({{ $materialCanvassPendCount }})
                                </li>
                                <li>
                                    <a href="/review/purchase_orders">Purchase Orders</a> ({{$purchaseOrderPendCount}})
                                </li>
                            </ul>
                        </li>
                        <li>
                            User Access
                            <ul>
                                <li>
                                    <a href="/access_codes">Access Codes</a>
                                </li>
                                <li>
                                    <a href="/roles">Roles</a>
                                </li>
                                <li>
                                    <a href="/user_roles">User Roles</a>
                                </li>
                                
                            </ul>
                        </li>
                        <li>
                            Master Data
                            <ul>
                                <li>
                                    <a href="/master_data/material/groups">Material Groups</a>
                                </li>
                                <li>
                                    <a href="/master_data/material/items">Material Items</a>
                                </li>
                                <li>
                                    <a href="/master_data/payment_terms">Payment Terms</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            Projects
                            <ul>
                                <li>
                                    <a href="/project/create">Create</a>
                                </li>
                                <li>
                                    <a href="/projects">List</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            Requests
                            <ul>
                                <li>
                                    <a href="/material_quantity_requests">Material Quantity Request</a>
                                </li>
                                <li>
                                    <a href="/material_canvass">Material Canvass</a>
                                </li>
                                <li>
                                    <a href="/purchase_orders">Purchase Order</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
