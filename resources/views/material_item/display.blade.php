@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/master_data/material_items">
                    <span>
                       Material Items
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Display
                    </span>	
                    <i class="ms-2 bi bi-display"></i>	
                </a>
            </li>
        </ul>
    </div>

<hr>
    <div class="form-container">
        <div class="form-header">
            Material Item
        </div>
        <div class="form-body">
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="form-group">
                        <label>Material Group</label>
                        <select disabled="true" class="form-control" id="materialGroup">
                            @foreach($materialGroups as $group)
                                <option value="{{$group->id}}" data-template="{{$group->specification_template ?? ''}}" @if($group->id == $materialItem->material_group_id) selected @endif >{{$group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-group">
                        <label>Material Name</label>
                        <input type="text" disabled="true" id="materialName" value="{{$materialItem->name}}" class="form-control"/>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-group">
                        <label>Specification / Unit Packaging</label>
                        <input type="text" disabled="true" readonly id="specificationUnitPackaging" value="{{$materialItem->specification_unit_packaging}}" class="form-control"/>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-group">
                        <label>Brand</label>
                        <input type="text" disabled="true" id="brandName" value="{{$materialItem->brand}}" class="form-control"/>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div id="noTemplateAlert" class="alert alert-warning d-none mb-0">
                        <strong><i class="fa fa-exclamation-triangle me-1"></i> Notice:</strong> The selected Material Group does not have a specification template configured. Material items cannot be edited without a specification template.
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
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                </div>
            </div>
            
        </div>
    </div>


</div>

<script type="module">
    import {$q} from '/adarna.js';

    let materialGroup               = $q('#materialGroup').first();
    let materialName                = $q('#materialName').first();
    let specificationUnitPackaging  = $q('#specificationUnitPackaging').first();
    let brandName                   = $q('#brandName').first();
    let editBtn                     = $q('#editBtn').first();
    let updateBtn                   = $q('#updateBtn').first();
    let cancelBtn                   = $q('#cancelBtn').first();
    let noTemplateAlert             = $q('#noTemplateAlert').first();
    let templateCard                = $q('#templateCard').first();
    let templateTextDisplay         = $q('#templateTextDisplay').first();
    let dynamicInputsArea           = $q('#dynamicInputsArea').first();

    let isEditable = false;

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

    function extractValues(template, fullValue) {
        const segments = parseTemplate(template);
        if (!segments.length || !fullValue) return [];

        let pattern = '^';
        const total = segments.length;
        for (let i = 0; i < total; i++) {
            const seg = segments[i];
            if (seg.type === 'static') {
                pattern += seg.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            } else {
                pattern += seg.datatype === 'number' ? '(\\d+(?:\\.\\d+)?)' : (i === total - 1 ? '([^\\r\\n]+)' : '([^\\r\\n]+?)');
            }
        }
        pattern += '$';

        try {
            const match = new RegExp(pattern).exec(fullValue.trim());
            if (!match) return [];
            return match.slice(1);
        } catch (e) {
            return [];
        }
    }

    function syncTemplateInputs() {
        const selectedOption = materialGroup.options[materialGroup.selectedIndex];
        const template = selectedOption ? (selectedOption.dataset.template || '').trim() : '';

        dynamicInputsArea.innerHTML = '';

        if (!template) {
            noTemplateAlert.classList.remove('d-none');
            templateCard.classList.add('d-none');
            specificationUnitPackaging.value = '';
            if (isEditable) updateBtn.disabled = true;
            return;
        }

        noTemplateAlert.classList.add('d-none');
        templateCard.classList.remove('d-none');
        templateTextDisplay.textContent = template;
        if (isEditable) updateBtn.disabled = false;

        const segments = parseTemplate(template);
        const extracted = extractValues(template, specificationUnitPackaging.value);
        let varIndex = 0;
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
                input.disabled = !isEditable;

                const val = extracted[varIndex] !== undefined ? extracted[varIndex] : '';
                input.value = val;
                varIndex++;

                input.addEventListener('input', updateCombined);
                inputs.push(input);

                col.appendChild(label);
                col.appendChild(input);
                row.appendChild(col);
            }
        }

        dynamicInputsArea.appendChild(row);
    }

    materialGroup.addEventListener('change', () => {
        specificationUnitPackaging.value = '';
        syncTemplateInputs();
    });

    syncTemplateInputs();

    editBtn.onclick = (e)=>{
        e.preventDefault();

        isEditable = true;
        editBtn.classList.add('d-none');

        materialGroup.disabled                  = false;
        materialName.disabled                   = false;
        brandName.disabled                      = false;

        syncTemplateInputs();
        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            window.util.navReload();
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/material/item/update',{
            name                            : materialName.value,
            brand                           : brandName.value,
            material_group_id               : materialGroup.value,
            specification_unit_packaging    : specificationUnitPackaging.value,
            id: '{{$materialItem->id}}'
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
        
        @if($back)
            window.util.navTo('/{{$back}}');
        @else
            window.util.navTo('/master_data/material/items');
        @endif
    }
</script>
</div>
@endsection