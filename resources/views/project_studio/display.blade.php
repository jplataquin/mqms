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
                    overflow:scroll;
                    min-width: 70%;
                    width: 70%;
                }

                #studio-side{
                    min-width: 30%;
                    width: 30%;
                    height: 100vh;
                    background:#708090;
                    border: 1px solid #696969;
                    overflow:scroll;
                    cursor: e-resize;
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
            </div>
            
            <div id="studio-editor">
            </div>

        </div>
    </div>
    
    <script type="module">
        
    </script>
    <script type="module">
        import {$q} from '/adarna.js';
        import ProjectTree from '/ui_components/ProjectTree.js';

        const studio_side = $q('#studio-side').first();
        const studio_editor = $q('#studio-editor').first();

        let mdown = false;
        let studio_side_width       = 30;
        let studio_editor_width     = 70;
        let studio_side_width_limit = 10;
        let studio_side_width_max   = 50;
        let width_increment         = 1;

        document.onmousedown = (e)=>{
            
            if(e.target.id == 'studio-side'){
                mdown = true;
            }
        }

        document.onmouseup = ()=>{
            
            mdown = false;
                
        }

        studio_editor.onmousein = ()=>{
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

    <script type="module">
        import {$q} from '/adarna.js';
        import ProjectTree from '/ui_components/ProjectTree.js';

        const side   = $q('#studio-side').first();
        const editor = $q('#studio-editor').first();

        ProjectTree({
            project_id:'{{$project->id}}'
        });

    </script>
</div>
@endsection