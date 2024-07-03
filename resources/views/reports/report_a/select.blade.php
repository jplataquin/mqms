@extends('layouts.app')

@section('content')
<div class="container">
<h5>Reports » A</h5>
<hr>

    <div class="row">
        <div class="col-lg-4">
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
                <label>Section</label>
                <select class="form-control" id="sectionSelect">
                </select>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <label>Component</label>
                <select class="form-control" id="componentSelect">
                </select>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-lg-12 text-end">
            <button id="generateBtn" class="btn btn-primary">Generate</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template,$el,$util} from '/adarna.js';

    let projectSelect   = $q('#projectSelect').first();
    let sectionSelect   = $q('#sectionSelect').first();
    let componentSelect = $q('#componentSelect').first();
    let generateBtn     = $q('#generateBtn').first();
    
    const t= new Template();

    projectSelect.onchange = (e)=>{

        e.preventDefault();

        sectionSelect.innerHTML = '';
        componentSelect.innerHTML = '';

        window.util.blockUI();

        window.util.$get('/api/section/list',{
            project_id: projectSelect.value,
            orderBy:'name',
            order:'ASC'
        }).then(reply=>{

            if(!reply.status){

                window.util.unblockUI()
                alert(reply.message);
                return false;
            }

            sectionSelect.append(
                t.option({value:''},' - ')
            );

            reply.data.forEach((item)=>{

                sectionSelect.append(
                    t.option({value:item.id},item.name)
                );

            });

            window.util.unblockUI();
        });
        }

        sectionSelect.onchange = (e)=>{

        e.preventDefault();

        componentSelect.innerHTML = '';

        window.util.blockUI();

        window.util.$get('/api/component/list',{
            section_id: sectionSelect.value,
            orderBy:'name',
            order:'ASC'
        }).then(reply=>{

            if(!reply.status){

                window.util.unblockUI()
                alert(reply.message);
                return false;
            }

            componentSelect.append(
                t.option({value:''},' - ')
            );

            reply.data.forEach((item)=>{

                componentSelect.append(
                    t.option({value:item.id},item.name)
                );

            });

            window.util.unblockUI();
        });
    }


    generateBtn.onclick = (e)=>{
        e.preventDefault();

        window.open('/report/a/generate/'+projectSelect.value+'/'+sectionSelect.value+'/'+componentSelect.value,'_blank');
    }
</script>
@endsection
