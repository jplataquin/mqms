@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/master_data/material/items">
                    <span>
                        Material Items
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>	
                    <i class="ms-2 bi bi-file-earmark-plus"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            Create Material Item
        </div>
        <div class="form-body">
            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Material Group</label>
                        <select class="form-control" id="materialGroup">
                            <option value="">-- Select Material Group --</option>
                            @foreach($materialGroups as $group)
                                <option value="{{$group->id}}" data-template="{{$group->specification_template ?? ''}}" @if($group->id == $material_group_id) selected @endif>{{$group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Material Name</label>
                        <input type="text" id="materialName" class="form-control"/>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Specification / Unit Packaging</label>
                        <input type="text" id="specificationUnitPackaging" readonly class="form-control" placeholder="Auto-generated from template"/>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Brand Name</label>
                        <input type="text" id="brandName" class="form-control"/>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div id="noTemplateAlert" class="alert alert-warning d-none mb-0">
                        <strong><i class="fa fa-exclamation-triangle me-1"></i> Notice:</strong> The selected Material Group does not have a specification template configured. Material items cannot be created for this group until a template is set in Material Groups.
                    </div>
                    <div id="templateCard" class="border rounded p-3 d-none">
                        <div class="fw-bold mb-2">Specification Format: <span id="templateTextDisplay" class="text-primary font-monospace"></span></div>
                        <div id="dynamicInputsArea"></div>
                    </div>
                </div>

            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="createBtn">Create</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    let createBtn                   = $q('#createBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let materialName                = $q('#materialName').first();
    let materialGroup               = $q('#materialGroup').first();
    let specificationUnitPackaging  = $q('#specificationUnitPackaging').first();
    let brandName                   = $q('#brandName').first();
    let noTemplateAlert             = $q('#noTemplateAlert').first();
    let templateCard                = $q('#templateCard').first();
    let templateTextDisplay         = $q('#templateTextDisplay').first();
    let dynamicInputsArea           = $q('#dynamicInputsArea').first();

    function parseTemplate(template) {
        if (!template || !template.trim()) return [];
        const regex = /\[([^:\]\r\n]+)(?::(number|text))?\]/g;
        const segments = [];
        let lastIndex = 0;
        let match;

        while ((match = regex.exec(template)) !== null) {
            if (match.index > lastIndex) {
                segments.push({
                    type: 'static',
                    value: template.substring(lastIndex, match.index)
                });
            }
            segments.push({
                type: 'variable',
                label: match[1].trim(),
                datatype: match[2] && match[2].toLowerCase() === 'number' ? 'number' : 'text'
            });
            lastIndex = regex.lastIndex;
        }

        if (lastIndex < template.length) {
            segments.push({
                type: 'static',
                value: template.substring(lastIndex)
            });
        }

        return segments;
    }

    function syncTemplateInputs() {
        const selectedOption = materialGroup.options[materialGroup.selectedIndex];
        const template = selectedOption ? (selectedOption.dataset.template || '').trim() : '';

        dynamicInputsArea.innerHTML = '';

        if (!template) {
            noTemplateAlert.classList.remove('d-none');
            templateCard.classList.add('d-none');
            specificationUnitPackaging.value = '';
            createBtn.disabled = true;
            return;
        }

        noTemplateAlert.classList.add('d-none');
        templateCard.classList.remove('d-none');
        templateTextDisplay.textContent = template;
        createBtn.disabled = false;

        const segments = parseTemplate(template);
        const inputs = [];

        function updateCombined() {
            let combined = '';
            let vIdx = 0;
            for (const seg of segments) {
                if (seg.type === 'static') {
                    combined += seg.value;
                } else {
                    const inp = inputs[vIdx++];
                    combined += (inp ? inp.value.trim() : '');
                }
            }
            specificationUnitPackaging.value = combined;
        }

        const row = document.createElement('div');
        row.className = 'row g-3 align-items-end';

        for (const seg of segments) {
            if (seg.type === 'static') {
                const col = document.createElement('div');
                col.className = 'col-auto';
                const span = document.createElement('span');
                span.className = 'badge bg-secondary bg-opacity-25 text-body px-2 py-2 fs-6';
                span.textContent = seg.value;
                col.appendChild(span);
                row.appendChild(col);
            } else {
                const col = document.createElement('div');
                col.className = 'col-auto';

                const label = document.createElement('label');
                label.className = 'form-label small text-muted mb-1 d-block';
                label.textContent = seg.label + (seg.datatype === 'number' ? ' (number)' : '');

                const input = document.createElement('input');
                input.type = seg.datatype === 'number' ? 'number' : 'text';
                if (seg.datatype === 'number') input.step = 'any';
                input.className = 'form-control';
                input.placeholder = seg.label;

                input.addEventListener('input', updateCombined);
                inputs.push(input);

                col.appendChild(label);
                col.appendChild(input);
                row.appendChild(col);
            }
        }

        dynamicInputsArea.appendChild(row);
        updateCombined();
    }

    materialGroup.addEventListener('change', syncTemplateInputs);
    syncTemplateInputs();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/material/item/create',{
            name: materialName.value,
            specification_unit_packaging: specificationUnitPackaging.value,
            material_group_id: materialGroup.value,
            brand: brandName.value
        }).then(reply=>{
            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };
      
             window.util.navTo('/master_data/material/item/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {

        @if($back)
            window.util.navTo('/{{$back}}');
        @else
            window.util.navTo('/master_data/materials');
        @endif    
    }

</script>
</div>
@endsection