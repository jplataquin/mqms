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
        import NodeItem from '/ui_components/NodeItem.js';

        const side   = $q('#studio-side').first();
        const editor = $q('#studio-editor').first();

        async function getChildren(type,id){
            return new Promise((resolve,reject)=>{

                window.util.$get('/api/project/studio/node/children',{
                    type:type,
                    id:id
                }).then(reply=>{

                    if(reply.status <= 0 ){
                        resolve(false);
                        return false;
                    }

                    let items = [];

                    if(reply.data.type == 'section'){
                        reply.data.items.map(item=>{
                            items.push(SectionNode(item));
                        });
                    }else if(reply.data.type == 'contract_item'){
                        reply.data.items.map(item=>{
                            items.push(ContractItemNode(item));
                        });
                    }else if(reply.data.type == 'component'){
                        reply.data.items.map(item=>{
                            items.push(ComponentNode(item));
                        });
                    }else if(reply.data.type == 'component_item'){
                        reply.data.items.map(item=>{
                            items.push(ComponentItemNode(item));
                        });
                    }

                    resolve(items);
                })
            });
        }

        function SectionNode(data){
            return new NodeItem({
                type:'section',
                id:data.id,
                name:data.name,
                status:data.status,
                parentContainer: side,
                onScreen:()=>{},
                open: async ()=>{
                    return getChildren('section',data.id);
                }
            })
        }

        function ContractItemNode(data){
            return new NodeItem({
                type:'contract_item',
                id:data.id,
                name:data.description,
                status:data.status,
                parentContainer: side,
                onScreen:()=>{},
                open: async ()=>{
                    return getChildren('contract_item',data.id);
                }
            })
        }

        function ComponentNode(data){
            return new NodeItem({
                type:'component',
                id:data.id,
                name:data.name,
                status:data.status,
                parentContainer: side,
                onScreen:()=>{},
                open: async ()=>{
                    return getChildren('component',data.id);
                }
            })
        }


        function ComponentItemNode(data){
            return new NodeItem({
                type:'component_item',
                id:data.id,
                name:data.name,
                status:data.status,
                parentContainer: side,
                onScreen:()=>{},
                open: async ()=>{
                    return getChildren('component_item',data.id);
                }
            })
        }

        const root = NodeItem({
            id:'{{$project->id}}',
            name:'{{$project->name}}',
            status:'{{$project->status}}',
            parentContainer: side,
            type:'project',
            onScreen:()=>{},
            open: async ()=>{

                return getChildren('project','{{$project->id}}')
                
            }
        });


        side.appendChild(root);
    </script>
</div>
@endsection