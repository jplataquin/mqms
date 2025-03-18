@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/project/{{$project->id}}">
                    <span>
                       Project
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Section
                    </span>
                    
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
    
    <hr>


    <div class="row">

        <div class="col-lg-12 mb-3">
            <table class="record-table-horizontal">
                <tbody>
                    <tr>
                        <th>Project</th>
                        <td>{{$project->name}}</td>
                    </tr>
                </tbody>
            </table>    
        </div>

    </div>

    <div class="form-container">
        <div class="form-header">
            Section
        </div>
        <div class="form-body">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="sectionName" value="{{$section->name}}" disabled="true" class="form-control"/>        
                    </div>
                </div>
                <div class="col-lg-6">
                    <label>ID</label>
                    <input type="text" value="{{str_pad($section->id,6,0,STR_PAD_LEFT)}}" disabled="true" class="form-control"/>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Gross Total Amount</label>
                        <input type="text" id="gross_total_amount" value="{{$section->gross_total_amount}}" disabled="true" class="form-control"/>        
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-12 text-end">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                    <button class="btn btn-warning" id="printBtn">Print</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <hr>
    
    <div>
        <div class="folder-form-container">
            <div class="folder-form-tab">
                Contract Items
            </div>
            <div class="folder-form-body">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="searchFilter" placeholder="Search Code Item / Description">
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 text-end">
                        <button id="createBtn" class="btn btn-warning">Create</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="contract_items_container" class="mt-3">
            @foreach($contract_items as $contract_item)

                <div class="item item-container fade-in" data-id="{{$contract_item->id}}">
                    <div class="item-header">
                        {{$contract_item->description}}
                    </div>
                        
                    <div class="item-body row">
                        <div class="col-4">
                            {{$contract_item->item_code}}
                        </div>
                        <div class="col-4">
                            {{$contract_item->item_type}}
                        </div>
                        <div class="col-4">
                            @if(isset($unit_options[ $contract_item->unit_id ]))
                                {{$contract_item->contract_quantity}} {{ $unit_options[ $contract_item->unit_id ]->text }}
                            @endif
                        </div>
                    </div>
                        
                </div>

            @endforeach
        </div>
    </div>

</div>   


<script type="module">
    import {$q,$el, Template} from '/adarna.js';
    import CreateContractItemForm from '/ui_components/create_forms/CreateContractItemForm.js';

    const sectionName                 = $q('#sectionName').first();
    const gross_total_amount          = $q('#gross_total_amount').first();
    const search_filter               = $q("#searchFilter").first();
    const contract_items_container    = $q('#contract_items_container').first();
    const editBtn                     = $q('#editBtn').first();
    const updateBtn                   = $q('#updateBtn').first();
    const cancelBtn                   = $q('#cancelBtn').first();
    const deleteBtn                   = $q('#deleteBtn').first();

    const createBtn                   = $q('#createBtn').first();
    const printBtn                    = $q('#printBtn').first();
    

    const unit_options             = @json($unit_options);
    const contract_items_record    = @json($contract_items);

    const t = new Template();


    window.util.numbersOnlyInput([gross_total_amount],{
        negative:false,
        precision:2
    });
    
    function escapeRegex(str) {
        return str.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
    };

    function setupRows(){
        $q('.item').apply((el)=>{

            el.onclick = (e)=>{
                window.util.navTo('/project/section/contract_item/'+el.getAttribute('data-id')) ;
            }
        });
    }

    setupRows();

    let throttle = false;

    search_filter.onkeyup = (e)=>{

        if(throttle) return true;
        
        throttle = true;

        setTimeout(()=>{
            

            contract_items_container.innerHTML = '';

            let query = search_filter.value;

            var re = new RegExp(escapeRegex(query), 'i');

            let result = contract_items_record.filter((item)=>{
                return item.description.match(re) || item.item_code.match(re);
            });

            if(!result){
                return false;
            }

            result.map(res=>{

                let row = t.div({class:'item item-container fade-in',dataId:res.id},()=>{
                        
                    t.div({class:'item-header'},res.description);

                    t.div({class:'item-body row'},()=>{
                        t.div({class:'col-4'},()=>{
                            t.txt(res.item_code);
                        });

                        t.div({class:'col-4'},()=>{
                            t.txt(res.item_type);
                        });

                        t.div({class:'col-4'},()=>{

                            if(typeof unit_options[res.unit_id] != 'undefined'){
                                t.txt(
                                    window.util.numberFormat(res.contract_quantity)+' '+unit_options[res.unit_id].text
                                );
                            }
                        });
                    });
                });

                contract_items_container.append(row);//append
            });
            setupRows();

            throttle = false;
        },500);
    }

    printBtn.onclick = (e)=>{
        e.preventDefault();
        window.open('/project/section/print/{{$section->id}}','_blank').focus();
    }

    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        sectionName.disabled = false;
     
        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }


    deleteBtn.onclick = async ()=>{

        let answer = await window.util.prompt('Are you sure you want to delete this Section? \n If so please type "{{$section->name}}"');
       
        if(answer != "{{$section->name}}"){
            window.util.alert('Error','Invalid answer');
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/section/delete',{
            id: "{{$section->id}}"
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navTo('/project/{{$project->id}}');
        });
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/section/update',{
            name        : sectionName.value,
            gross_total_amount: window.util.pureNumber(gross_total_amount.value,2),
            id          : '{{$section->id}}'
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0){
                window.util.showMsg(reply);
                return false;
            }

            window.util.navReload();
        });
    }

    cancelBtn.onclick = (e)=>{
        window.util.navTo('/project/{{$project->id}}');
    }


    createBtn.onclick = ()=>{

        let create_contract_item_form = CreateContractItemForm({
            section_id:'{{$section->id}}',
            unit_options: @json($unit_options)
        });

        window.util.drawerModal.content('Create Contract Item',create_contract_item_form).open();
       
    }

    
</script>
</div>
@endsection