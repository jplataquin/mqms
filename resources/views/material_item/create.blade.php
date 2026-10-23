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
                                <option value="{{$group->id}}" @if($group->id == $material_group_id) selected @endif>{{$group->name}}</option>
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
                        <label>Brand Name</label>
                        <input type="text" id="brandName" class="form-control"/>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Specification / Unit Packaging</label>
                        <input type="text" id="specificationUnitPackaging" readonly class="form-control"/>
                    </div>
                </div>

            </div>

            <!-- Warning if Material Group has no template -->
            <div id="noTemplateAlert" class="alert alert-warning border border-warning bg-warning bg-opacity-10 d-none mt-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Specification Template Missing:</strong>
                <span>The selected Material Group does not have a specification template configured. Items cannot be created until a template is defined on the Material Group.</span>
            </div>

            <!-- Dynamic Template Builder -->
            <div id="templateBuilderBox" class="border rounded p-3 mt-3 d-none">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0">
                        <i class="bi bi-ui-checks-grid me-2 text-primary"></i>Specification Builder
                    </h6>
                    <span class="badge bg-secondary" id="templateBadge"></span>
                </div>
                <p class="text-muted small mb-3">
                    Fill in the required template fields below. The final specification will be formatted automatically.
                </p>
                <div id="dynamicInputsRow" class="row g-2 align-items-end"></div>
                <div id="templateValidationMsg" class="small mt-2"></div>
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

<script>
    window.MATERIAL_GROUPS = @json($materialGroups);
</script>

<script type="module">
    import {$q} from '/adarna.js';

    const createBtn                  = $q('#createBtn').first();
    const cancelBtn                  = $q('#cancelBtn').first();
    const materialName               = $q('#materialName').first();
    const materialGroup              = $q('#materialGroup').first();
    const specificationUnitPackaging = $q('#specificationUnitPackaging').first();
    const brandName                  = $q('#brandName').first();
    const noTemplateAlert            = $q('#noTemplateAlert').first();
    const templateBuilderBox         = $q('#templateBuilderBox').first();
    const templateBadge              = $q('#templateBadge').first();
    const dynamicInputsRow           = $q('#dynamicInputsRow').first();
    const templateValidationMsg      = $q('#templateValidationMsg').first();

    const groupsMap = {};
    (window.MATERIAL_GROUPS || []).forEach(g => {
        groupsMap[g.id] = g;
    });

    let activeSegments = [];
    let activeRegex = null;

    function parseTemplate(template) {
        if (!template || !template.trim()) {
            return [];
        }
        const regex = /\[([^:\]\r\n]+)(?::(number|text))?\]/g;
        let segments = [];
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

    function buildRegex(segments) {
        if (!segments || segments.length === 0) return null;
        let pattern = '^';
        const total = segments.length;
        for (let i = 0; i < total; i++) {
            const seg = segments[i];
            if (seg.type === 'static') {
                pattern += seg.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            } else if (seg.type === 'variable') {
                if (seg.datatype === 'number') {
                    pattern += '(\\d+(?:\\.\\d+)?)';
                } else {
                    pattern += (i === total - 1) ? '([^\\r\\n]+)' : '([^\\r\\n]+?)';
                }
            }
        }
        pattern += '$';
        try {
            return new RegExp(pattern, 'u');
        } catch (e) {
            return new RegExp(pattern);
        }
    }

    function assembleAndValidate() {
        if (!activeSegments.length) {
            specificationUnitPackaging.value = '';
            return;
        }

        let assembled = '';
        let varIdx = 0;
        let allVarsFilled = true;

        activeSegments.forEach(seg => {
            if (seg.type === 'static') {
                assembled += seg.value;
            } else if (seg.type === 'variable') {
                const input = dynamicInputsRow.querySelector(`[data-var-index="${varIdx}"]`);
                const val = input ? input.value : '';
                if (!val || !val.trim()) {
                    allVarsFilled = false;
                }
                assembled += val;
                varIdx++;
            }
        });

        specificationUnitPackaging.value = assembled;

        if (!allVarsFilled) {
            templateValidationMsg.className = 'small mt-2 text-warning';
            templateValidationMsg.textContent = 'Please fill in all template fields.';
            return;
        }

        if (activeRegex && activeRegex.test(assembled.trim())) {
            templateValidationMsg.className = 'small mt-2 text-success';
            templateValidationMsg.textContent = '✓ Specification matches the required group template.';
        } else {
            templateValidationMsg.className = 'small mt-2 text-danger';
            templateValidationMsg.textContent = '⚠ Specification does not match the required format.';
        }
    }

    function renderDynamicBuilder(template) {
        dynamicInputsRow.innerHTML = '';
        activeSegments = parseTemplate(template);
        activeRegex = buildRegex(activeSegments);

        let varIdx = 0;
        activeSegments.forEach((seg, idx) => {
            if (seg.type === 'static') {
                const col = document.createElement('div');
                col.className = 'col-auto d-flex align-items-center pb-2 text-muted fw-bold';
                col.style.whiteSpace = 'pre';
                col.textContent = seg.value;
                dynamicInputsRow.appendChild(col);
            } else if (seg.type === 'variable') {
                const col = document.createElement('div');
                col.className = 'col-sm-3 col-6';

                const group = document.createElement('div');
                group.className = 'form-group mb-0';

                const label = document.createElement('label');
                label.className = 'small fw-bold text-truncate w-100 mb-1';
                label.textContent = seg.label + (seg.datatype === 'number' ? ' (#)' : '');

                const input = document.createElement('input');
                input.className = 'form-control form-control-sm';
                input.type = seg.datatype === 'number' ? 'number' : 'text';
                if (seg.datatype === 'number') {
                    input.step = 'any';
                }
                input.placeholder = seg.label;
                input.setAttribute('data-var-index', String(varIdx));
                input.addEventListener('input', assembleAndValidate);

                group.appendChild(label);
                group.appendChild(input);
                col.appendChild(group);
                dynamicInputsRow.appendChild(col);

                varIdx++;
            }
        });

        assembleAndValidate();
    }

    function onGroupChanged() {
        const groupId = materialGroup.value;
        const group = groupsMap[groupId];

        if (!groupId || !group) {
            noTemplateAlert.classList.add('d-none');
            templateBuilderBox.classList.add('d-none');
            specificationUnitPackaging.value = '';
            createBtn.disabled = true;
            return;
        }

        const template = group.specification_template ? group.specification_template.trim() : '';

        if (!template) {
            noTemplateAlert.classList.remove('d-none');
            templateBuilderBox.classList.add('d-none');
            specificationUnitPackaging.value = '';
            createBtn.disabled = true;
        } else {
            noTemplateAlert.classList.add('d-none');
            templateBuilderBox.classList.remove('d-none');
            templateBadge.textContent = 'Template: ' + template;
            createBtn.disabled = false;
            renderDynamicBuilder(template);
        }
    }

    materialGroup.onchange = onGroupChanged;

    // Trigger initial check
    onGroupChanged();

    createBtn.onclick = (e) => {
        if (!materialGroup.value) {
            window.util.showMsg({ status: -2, message: 'Please select a Material Group.' });
            return;
        }

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
