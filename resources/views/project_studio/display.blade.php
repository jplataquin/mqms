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


        <style>
                #studio-container {
                    background-color: DodgerBlue;
                    display: flex;
                }

                #studio-editor {
                    flex-grow: 1;
                    height: 100vh;
                    background:#9999;
                    text-align: right;
                    min-width: 70%;
                    width: 70%;
                }

                #studio-side{
                    min-width: 30%;
                    width: 30%;
                    height: 100vh;
                    background:#708090;
                    border: 1px solid #696969;
                    color:yellow;
                    cursor: move;
                }

                .item{
                    border: 1px solid #DCDCDC;
                    border-radius: .4em;
                    background-color: white;
                    color: #000;
                    padding:12px;
                    margin:10px;
                    display: inline-block;
                }

                .item:hover{
                    cursor:grab;
                }
        </style>
        <div id="studio-container">
  
            <div id="studio-side">
                 <h3 id="title">I'm resizable</h3>
                <i class="fa fa-align-center item"> Section 1</i>
                <i class="fa fa-align-center item"> Section 2</i>
                <i class="fa fa-align-center item"> Section 3</i>
                <i class="fa fa-align-center item"> Section 4</i>
                <i class="fa fa-align-center item"> Section 5</i>
                <i class="fa fa-align-center item"> Section 6</i>

            </div>
            
            <div id="studio-editor">
                <h3 id="title">I'm editable</h3>
            </div>

        </div>
    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const studio_side = $q('#studio-side').first();
        const studio_editor = $q('#studio-editor').first();

        let mdown = false;
        let studio_side_width       = 30;
        let studio_editor_width     = 70;
        let studio_side_width_limit = 10;
        let studio_side_width_max   = 50;
        let width_increment         = 1;

        studio_side.onmousedown = ()=>{
            
            mdown = true;
        }

        studio_side.onmouseup = ()=>{
            
            mdown = false;
                
        }

        studio_side.onmouseout = ()=>{
            mdown = false;
        }

        document.onmousemove = (e) =>{
            
            if(!mdown) return false;

            if(e.movementX == -1){

                studio_side_width = studio_side_width - width_increment;

                if(studio_side_width <= studio_side_width_limit){
                    studio_side_width = studio_side_width_limit;
                }

                studio_editor_width = 100 - studio_side_width;

                studio_side.style.minWidth   = studio_side_width+'%';
                studio_side.style.width      = studio_side_width+'%';
                studio_side.style.maxWidth   = studio_side_width+'%';

                studio_editor.style.minWidth    = studio_editor_width+'%';
                studio_editor.style.width       = studio_editor_width+'%';
                studio_editor.style.maxWidth    = studio_editor_width+'%';

            }else if (e.movementX == 1){
                
                studio_side_width = studio_side_width + width_increment;

                if(studio_side_width >= studio_side_width_max){
                    studio_side_width = studio_side_width_max;
                }

                studio_editor_width = 100 - studio_side_width;

                studio_side.style.minWidth   = studio_side_width+'%';
                studio_side.style.width      = studio_side_width+'%';
                studio_side.style.maxWidth   = studio_side_width+'%';

                studio_editor.style.minWidth    = studio_editor_width+'%';
                studio_editor.style.width       = studio_editor_width+'%';
                studio_editor.style.maxWidth    = studio_editor_width+'%';
                
            }
        }

    </script>
</div>
@endsection