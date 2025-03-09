@extends('layouts.project_studio')

@section('content')
<div id="content">
    <div class="" id="studio">

        <style>
                #studio-container {
                    display: flex;
                }

                #studio-editor {
                    flex-grow: 1;
                    height: 100vh;
                    overflow-x:hidden;
                    overflow-y:scroll;
                    min-width: 80%;
                    width: 80%;
                    padding:1em;
                }

                #size-handle{
                   height:100vh;
                   background-color:pink;
                   min-width:3px;
                   cursor: e-resize;
                }

                #studio-side{
                    min-width: 20%;
                    width: 20%;
                    height: 100vh;
                    background:#708090;
                    overflow:scroll;
                    user-select: none;
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
            <div id="size-handle"></div>
            <div id="studio-editor">
            </div>

        </div>
    </div>
    
    <script type="module">
        import {$q} from '/adarna.js';

        const studio_side   = $q('#studio-side').first();
        const studio_editor = $q('#studio-editor').first();
        const size_handle   = $q('#size-handle').first();

        let mdown = false;
        let studio_side_width       = 30;
        let studio_editor_width     = 70;
        let studio_side_width_limit = 100;
        let studio_side_width_max   = 500;
        let width_increment         = 1;

        document.onmousedown = (e)=>{
            
            if(e.target.id == 'size-handle'){
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


            studio_side_width = e.pageX;//studio_side_width - width_increment;

            if(studio_side_width <= studio_side_width_limit){
                studio_side_width = studio_side_width_limit;
            }

            if(studio_side_width >= studio_side_width_max){
                studio_side_width = studio_side_width_max;
            }

            studio_side.style.minWidth   = studio_side_width+'px';
            studio_side.style.width      = studio_side_width+'px';
            studio_side.style.maxWidth   = studio_side_width+'px';

        }

    </script>
         
    <script type="module">
        import {$q,Template} from '/adarna.js';
        import NodeItem from '/ui_components/NodeItem.js';

        const side          = $q('#studio-side').first();
        const editor        = $q('#studio-editor').first();
        

        studio.unit_options = @json($unit_options);

        let screen_url      = '';
        
        studio.onScreen = (url)=>{

            if(!url){
                console.error('URL not defined');
                return false;
            }

            if(url == screen_url) return false;

            editor.innerHTML = '';

            window.util.$content(url,{},{
                'X-STUDIO-MODE':true
            }).then(reply=>{

                if(reply.status <= 0){
                    window.util.showMsg(reply);
                    return false;
                }
                
                let content = $q('#content',reply.data).first();

                if(!content){
                    window.util.alert('Error','No content found');
                    return false;
                }

                $q('script',content).items().map(script=>{

                    const newScript = document.createElement('script');

                    Array.from(script.attributes).forEach((attr) => {
                        newScript.setAttribute(attr.name, attr.value)
                    });

                    newScript.textContent   = script.textContent
                    newScript.async         = false;

                    const parent = script.parentNode;
                    parent.insertBefore(newScript, script);

                    script.remove();
                });

                editor.appendChild(content);
            });
            
        }

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
                studio:studio,
                id:data.id,
                section_id: data.id,
                name:data.name,
                status:data.status,
                parentContainer: side,
                successAddChild:(type,rdata,node)=>{
                    
                    rdata.project_id       = '{{$project->id}}';
                    rdata.section_id       = data.id;
                    rdata.contract_item_id = rdata.id;
                  
                    let item = ContractItemNode(rdata);

                    node.handler.prependChild(item);

                    item.handler.focus();
                },
                onScreen:()=>{
                    studio.onScreen('/project/section/'+data.id);
                },
                open: async ()=>{
                    return getChildren('section',data.id);
                }
            })
        }

        function ContractItemNode(data){
          
            return new NodeItem({
                type:'contract_item',
                project_id:'{{$project->id}}',
                contract_item_id: data.id,
                section_id: data.section_id,
                studio:studio,
                id:data.id,
                name:data.description,
                status:data.status,
                parentContainer: side,
                successAddChild:(type,rdata,node)=>{

                    rdata.project_id       = '{{$project->id}}';
                    rdata.section_id       = data.section_id;
                    rdata.contract_item_id = data.id;
                  
                    let item = ComponentNode(rdata);

                    node.handler.prependChild(item);

                    item.handler.focus();
                },
                onScreen:()=>{
                    studio.onScreen('/project/section/contract_item/'+data.id);
                },
                open: async ()=>{
                    return getChildren('contract_item',data.id);
                }
            })
        }

        function ComponentNode(data){

            return new NodeItem({
                type:'component',
                project_id          :'{{$project->id}}',
                section_id          : data.section_id,
                contrac_item_id     : data.contract_item_id,
                component_id        : data.id,
                component_unit_text : data.unit_text,
                component_quantity  : data.quantity,
                component_use_count : data.use_count,
                studio:studio,
                id:data.id,
                name:data.name,
                status:data.status,
                parentContainer: side,
                successAddChild:(type,rdata,node)=>{

                    
                    rdata.project_id       = '{{$project->id}}';
                    rdata.component_id     = rdata.component_id;

                    let item = ComponentItemNode(rdata);

                    node.handler.prependChild(item);

                    item.handler.focus();
                },
                onScreen:()=>{
                    studio.onScreen('/project/section/contract_item/component/'+data.id);
                },
                open: async ()=>{
                    return getChildren('component',data.id);
                }
            })
        }


        function ComponentItemNode(data){
           
            return new NodeItem({
                type:'component_item',
                studio:studio,
                id:data.id,
                name:data.name,
                status:data.status,
                parentContainer: side,
                successAddChild:(type,data,node)=>{
              
                },
                onScreen:()=>{
                    studio.onScreen('/project/section/contract_item/component/component_item/'+data.id);
                },
                open: async ()=>{
                    return getChildren('component_item',data.id);
                }
            })
        }

        const root = NodeItem({
            id:'{{$project->id}}',
            studio:studio,
            name:'{{$project->name}}',
            status:'{{$project->status}}',
            parentContainer: side,
            type:'project',
            successAddChild:(type,data,node)=>{
                let item = SectionNode(data);

                node.handler.prependChild(item);

                item.handler.focus();
            },
            onScreen:()=>{
                studio.onScreen('/project/{{$project->id}}');
            },
            open: async ()=>{

                return getChildren('project','{{$project->id}}')
                
            }
        });

        side.innerHTML = '';
        side.appendChild(root);
    </script>
</div>
@endsection