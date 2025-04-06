@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/review/purchase_orders">
                        <span>
                        Review
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Material Budget
                        </span>
                        <i class="ms-2 bi bi-list-ul"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container">
            <div class="folder-form-tab">
                Review Purchase Orders
            </div>
            <div class="folder-form-body">
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group">
                            <label>Project</label>
                            <select class="form-control" id="projectSelect">
                                <option value=""> - </option>
                                @foreach($projects as $project)
                                    <option value="{{$project->id}}">{{$project->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> 
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button id="searchBtn" class="btn w-100 btn-primary">Search</button>
                        </div>                
                    </div>   
                </div>

            </div>
        </div>

        
    </div>
</div>
@endsection