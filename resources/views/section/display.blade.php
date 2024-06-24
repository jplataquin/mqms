@extends('layouts.app')

@section('content')
<div class="container">
<!-- <h6>Project » Section ( {{$section->id}} )</h6> -->
<hr>

    <div class="row">

        <div class="col-lg-12">
            <table class="w-100 table">
                <tbody>
                    <tr>
                        <th>Project</th>
                        <td>{{$project->name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>
                            <input type="text" id="sectionName" value="{{$section->name}}" disabled="true" class="form-control"/>
                        </td>
                    </tr>
                </tbody>
            </table>    
        </div>

    </div>

    <div class="row mt-5">
        <div class="col-lg-6">
            <button class="btn btn-danger" id="deleteBtn">Delete</button>
          
        </div>
        <div class="col-lg-6 text-end">
           
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-primary" id="editBtn">Edit</button>
            <button class="btn btn-warning d-none" id="updateBtn">Update</button>
        </div>
    </div>

    <hr>
    
    <div class="mt-3">
        
        <div class="">
            <h3>Components</h3>
        </div>

        <div class="row">
            <div class="col-lg-8 col-sm-12">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <input type="text" class="form-control" id="component" />
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button id="createBtn" class="btn w-100 btn-warning">Create</button>
                </div> 
            </div>
        </div>

        <div id="component_list" class="mt-3">
            @foreach($components as $component)

                <div class="item row selectable-div fade-in border mb-3" data-id="{{$component->id}}">
                    <div class="col-lg-12">
                        <h3>{{$component->name}}</h3>
                    </div>
                </div>

            @endforeach
        </div>

    </div>
</div>

<script type="module">
    import {$q,$el, Template} from '/adarna.js';

    let sectionName                 = $q('#sectionName').first();
    let editBtn                     = $q('#editBtn').first();
    let updateBtn                   = $q('#updateBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let deleteBtn                   = $q('#deleteBtn').first();
    let component                   = $q('#component').first();
    let component_list              = $q('#component_list').first();
    let createBtn                   = $q('#createBtn').first();

    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        sectionName.disabled = false;
     
        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }


    deleteBtn.onclick = ()=>{

        let answer = prompt('Are you sure you want to delete this Section? \n If so please type "{{$section->name}}"');

        if(answer != "{{$section->name}}"){
            window.util.showMsg('Invalid answer');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/section/delete',{
            id: "{{$section->id}}"
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            window.location.href = '/project/{{$project->id}}';
        });
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/section/update',{
            name        : sectionName.value,
            id          : '{{$section->id}}'
        }).then(reply=>{

            if(reply.status <= 0){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            }

            document.location.reload(true);
        });
    }

    cancelBtn.onclick = (e)=>{
        document.location.href = '/project/{{$project->id}}';
    }


    function Component(id){

        const t = new Template();

        let name = t.h3('Loading...');
        let count = t.h6('Loading...');

        let el = t.div({class:'row selectable-div fade-in border mb-3',dataId:id},()=>{
            t.div({class:'col-lg-12'},(el)=>{
                el.append(name);
                el.append(count);
            });
        });


        window.util.$get('/api/component',{
            id:id
        }).then(reply=>{

            if(!reply.status){

                alert(reply.message);
                return false;
            }

            name.innerText = reply.data.name;
            count.innerText = 'Items: '+reply.data.component_items_count;

            el.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
        });


        el.onclick = ()=>{
            document.location.href = '/component/'+id;
        }

        return el;
    }


    createBtn.onclick = ()=>{

        window.util.blockUI();

        window.util.$post('/api/component/create',{
            section_id: '{{$section->id}}',
            name: component.value
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply.message);
                return false;
            }

            $el.append(Component(reply.data.id)).to(component_list);
        });

    }

    $q('.item').apply((el)=>{

        el.onclick = (e)=>{
            document.location.href = '/component/'+el.getAttribute('data-id');
        }
    });

</script>

@endsection