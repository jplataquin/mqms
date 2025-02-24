@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/projects">
                        <span>
                        Projects
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            Studio
                        </span>
                        <i class="ms-2 bi bi-display"></i>	
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <div id="project_studio_left_window" width="10%" class="d-inline">
                L
            </div>
            <div id="project_studio_right_window" class="overflow-scroll d-inline">
                R
            </div>
        </div>
    </div>
</div>
@endsection