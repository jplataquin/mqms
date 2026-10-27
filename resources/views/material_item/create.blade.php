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

        const segments = [];
        const len = template.length;
        let i = 0;
        let currentStatic = '';

        function unescapeTemplate(str) {
            if (!str) return '';
            return str.replace(/\\([\s\S])/g, function(match, char) {
                if (char === '[' || char === ']' || char === '\\' || char === ':') {
                    return char;
                }
                return match;
            });
        }

        function pushStatic(text) {
            if (!text) return;
            if (segments.length > 0 && segments[segments.length - 1].type === 'static') {
                segments[segments.length - 1].value += text;
            } else {
                segments.push({
                    type: 'static',
                    value: text
                });
            }
        }

        while (i < len) {
            const char = template[i];

            if (char === '\\' && i + 1 < len) {
                const next = template[i + 1];
                if (next === '[' || next === ']' || next === '\\' || next === ':') {
                    currentStatic += next;
                } else {
                    currentStatic += '\\' + next;
                }
                i += 2;
                continue;
            }

            if (char === '[') {
                let j = i + 1;
                let foundClose = false;
                let hasNestedOpen = false;

                while (j < len) {
                    const c = template[j];
                    if (c === '\r' || c === '\n') {
                        break;
                    }
                    if (c === '\\' && j + 1 < len) {
                        j += 2;
                        continue;
                    }
                    if (c === '[') {
                        hasNestedOpen = true;
                        break;
                    }
                    if (c === ']') {
                        foundClose = true;
                        break;
                    }
                    j++;
                }

                if (foundClose && !hasNestedOpen) {
                    const content = template.substring(i + 1, j);

                    let colonPos = -1;
                    let k = 0;
                    while (k < content.length) {
                        if (content[k] === '\\' && k + 1 < content.length) {
                            k += 2;
                            continue;
                        }
                        if (content[k] === ':') {
                            colonPos = k;
                        }
                        k++;
                    }

                    let isValid = true;
                    let label = '';
                    let datatype = 'text';

                    if (colonPos !== -1) {
                        const typePart = content.substring(colonPos + 1).trim().toLowerCase();
                        if (typePart === 'number' || typePart === 'text') {
                            datatype = typePart;
                            label = unescapeTemplate(content.substring(0, colonPos).trim());
                        } else {
                            isValid = false;
                        }
                    } else {
                        label = unescapeTemplate(content.trim());
                    }

                    if (isValid && label.length > 0) {
                        if (currentStatic.length > 0) {
                            pushStatic(currentStatic);
                            currentStatic = '';
                        }
                        segments.push({
                            type: 'variable',
                            label: label,
                            datatype: datatype
                        });
                        i = j + 1;
                        continue;
                    }
                }

                currentStatic += '[';
                i++;
                continue;
            }

            currentStatic += char;
            i++;
        }

        if (currentStatic.length > 0) {
            pushStatic(currentStatic);
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
                input.type = 'text';
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