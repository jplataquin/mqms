@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/roles">
                    <span>
                       Roles
                    </span>                    
                    
                </a>
            </li>
            <li>
                <a href="#" class="active" >
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
            Role
        </div>
        <div class="form-body">
            <div class="row">

                <div class="col-12 mb-3">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" disabled="true" value="{{$role->name}}" id="name" class="form-control"/>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="description" disabled="true" class="form-control">{{$role->description}}</textarea>
                    </div>
                </div>
            </div>

            <div class="row mt-5 mb-3">
                <div class="col-6 text-start">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                </div>
            </div>
        </div>
    </div>
    <hr>

    <div class="row mt-4 mb-3 align-items-center">
        <div class="col-md-6 col-lg-5 mb-2 mb-md-0">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="searchAccessCode" class="form-control" placeholder="Search access codes by code or description..." autocomplete="off" />
                <button class="btn btn-outline-secondary d-none" type="button" id="clearSearchBtn" title="Clear search"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div class="col-md-6 col-lg-7 d-flex justify-content-between justify-content-md-end align-items-center gap-3">
            <span class="text-muted small" id="accessCodeCount"></span>
            <button class="btn btn-primary" id="openAddAccessCodeModalBtn" data-bs-toggle="modal" data-bs-target="#addAccessCodeModal">
                <i class="bi bi-plus-lg me-1"></i> Add Access Code
            </button>
        </div>
    </div>

    <!-- Modal for adding access code -->
    <div class="modal fade" id="addAccessCodeModal" tabindex="-1" aria-labelledby="addAccessCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccessCodeModalLabel">
                        <i class="bi bi-shield-lock me-2 text-primary"></i>Add Access Code
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Search Access Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input list="modal-access-code-list" id="modalAccessCode" class="form-control" placeholder="Type to search access code..." autocomplete="off" />
                            <button class="btn btn-outline-secondary d-none" type="button" id="clearModalAccessCodeBtn" title="Clear"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <datalist id="modal-access-code-list">
                            @foreach($accessCodes as $code)
                                <option class="modal-code-option" value="{{$code->code}}" data-id="{{$code->id}}">{{$code->description}}</option>
                            @endforeach
                        </datalist>
                        <small class="form-text text-muted mt-1 d-block" id="modalAccessCodeHelp">Select an access code from the suggestions.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="modalAddCodeBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container" id="list"></div>


</div>

<script type="module">
    import {$q,$el,Template} from '/adarna.js';

    let cancelBtn               = $q('#cancelBtn').first();
    let name                    = $q('#name').first();
    let description             = $q('#description').first();
    let editBtn                 = $q('#editBtn').first();
    let updateBtn               = $q('#updateBtn').first();
    let modalAddCodeBtn         = $q('#modalAddCodeBtn').first();
    let modalAccessCode         = $q('#modalAccessCode').first();
    let clearModalAccessCodeBtn = $q('#clearModalAccessCodeBtn').first();
    let modalAccessCodeHelp     = $q('#modalAccessCodeHelp').first();
    let addAccessCodeModalEl    = document.getElementById('addAccessCodeModal');
    let list                    = $q('#list').first();
    let searchAccessCode        = $q('#searchAccessCode').first();
    let clearSearchBtn          = $q('#clearSearchBtn').first();
    let accessCodeCount         = $q('#accessCodeCount').first();
    let totalAssignedCount      = null;
    let activeSearchSeq         = 0;

    function debounce(fn, delay = 300) {
        let timer = null;
        const debounced = function(...args) {
            if (timer) clearTimeout(timer);
            timer = setTimeout(() => {
                timer = null;
                fn.apply(this, args);
            }, delay);
        };
        debounced.cancel = () => {
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
        };
        return debounced;
    }

    const debouncedSearch = debounce((query) => {
        showData(query, false);
    }, 300);

    function reinitalize(){
        if(debouncedSearch){
            debouncedSearch.cancel();
        }
        searchAccessCode.value = '';
        if(clearSearchBtn){
            clearSearchBtn.classList.add('d-none');
        }
        $el.clear(list);
    }

    if(clearModalAccessCodeBtn){
        clearModalAccessCodeBtn.onclick = () => {
            modalAccessCode.value = '';
            clearModalAccessCodeBtn.classList.add('d-none');
            modalAccessCodeHelp.textContent = 'Select an access code from the suggestions.';
            modalAccessCodeHelp.classList.remove('text-danger');
            modalAccessCodeHelp.classList.add('text-muted');
            modalAccessCode.focus();
        };
    }

    if(modalAccessCode){
        modalAccessCode.oninput = () => {
            const val = (modalAccessCode.value || '').trim();
            if(val){
                clearModalAccessCodeBtn.classList.remove('d-none');
            } else {
                clearModalAccessCodeBtn.classList.add('d-none');
            }

            let opt = $q('.modal-code-option[value="'+modalAccessCode.value+'"]').first();
            if(opt){
                modalAccessCodeHelp.textContent = opt.textContent || 'Valid access code selected.';
                modalAccessCodeHelp.classList.remove('text-danger');
                modalAccessCodeHelp.classList.add('text-muted');
            } else if(val){
                modalAccessCodeHelp.textContent = 'Typing code... Please pick an existing code from suggestions.';
                modalAccessCodeHelp.classList.remove('text-danger');
                modalAccessCodeHelp.classList.add('text-muted');
            } else {
                modalAccessCodeHelp.textContent = 'Select an access code from the suggestions.';
                modalAccessCodeHelp.classList.remove('text-danger');
                modalAccessCodeHelp.classList.add('text-muted');
            }
        };

        modalAccessCode.onkeydown = (e) => {
            if(e.key === 'Enter'){
                e.preventDefault();
                modalAddCodeBtn.click();
            }
        };
    }

    if(addAccessCodeModalEl){
        addAccessCodeModalEl.addEventListener('shown.bs.modal', () => {
            modalAccessCode.value = '';
            clearModalAccessCodeBtn.classList.add('d-none');
            modalAccessCodeHelp.textContent = 'Select an access code from the suggestions.';
            modalAccessCodeHelp.classList.remove('text-danger');
            modalAccessCodeHelp.classList.add('text-muted');
            modalAccessCode.focus();
        });
    }

    modalAddCodeBtn.onclick = (e) => {
        let opt = $q('.modal-code-option[value="'+modalAccessCode.value+'"]').first();

        if(!opt){
            modalAccessCodeHelp.textContent = 'Access code not found. Please select a valid code from the list.';
            modalAccessCodeHelp.classList.remove('text-muted');
            modalAccessCodeHelp.classList.add('text-danger');
            modalAccessCode.focus();
            return false;
        }

        const accessCodeId = opt.getAttribute('data-id');

        window.util.blockUI();

        window.util.$post('/api/role/access_code/add',{
            role_id: '{{$role->id}}',
            access_code_id: accessCodeId
        }).then(reply=>{
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            // Close modal
            if(window.bootstrap && window.bootstrap.Modal){
                const modalInstance = bootstrap.Modal.getInstance(addAccessCodeModalEl) || bootstrap.Modal.getOrCreateInstance(addAccessCodeModalEl);
                if(modalInstance){
                    modalInstance.hide();
                }
            }

            modalAccessCode.value = '';
            reinitalize();
            showData('', true);
        }).catch(err => {
            window.util.unblockUI();
            console.error(err);
        });
    };

    editBtn.onclick = (e) =>{
        e.preventDefault();
        
        name.disabled = false;
        description.disabled = false;
        updateBtn.classList.remove('d-none');
        editBtn.classList.add('d-none');
    }

    updateBtn.onclick = (e) => {

        e.preventDefault();

        window.util.blockUI();

        window.util.$post('/api/role/update',{
            name: name.value,
            description: description.value,
            id: '{{$role->id}}'
        }).then(reply=>{

            window.util.unblockUI();
                
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };


            window.util.navReload();


        });
    }

    deleteBtn.onclick = (e) =>{
        e.preventDefault();

        let answer = window.util.promot('To delete record type in "{{$role->name}}"');

        if(answer != '{{$role->name}}'){
            alert('Invalid input');
            return false;
        }


        window.util.$post('/api/role/delete',{
            id: '{{$role->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };

            window.util.showMsg('/roles');

        });

    }

    cancelBtn.onclick = (e) => {
        
        window.util.navTo('/roles');

    }


    const t = new Template();

    function renderRows(data){
        $el.clear(list);

        if(!data || data.length === 0){
            const query = (searchAccessCode.value || '').trim();
            const msg = query 
                ? `No access codes match "${query}".`
                : 'No access codes assigned to this role.';
            let emptyMsg = t.div({class:'text-center p-4 text-muted border rounded my-3'}, msg);
            $el.append(emptyMsg).to(list);
            return;
        }

        const groups = {};
        data.forEach(item => {
            const parts = (item.code || '').split(':');
            const groupKey = (parts[0] || 'other').trim();
            if (!groups[groupKey]) {
                groups[groupKey] = [];
            }
            groups[groupKey].push(item);
        });

        Object.keys(groups).sort().forEach(groupName => {
            const items = groups[groupName];

            let groupContainer = t.div({class: 'folder-form-container access-code-group mb-4', 'data-group': groupName}, () => {
                t.div({class: 'folder-form-tab text-uppercase'}, groupName);
                t.div({class: 'folder-form-body'}, () => {
                    items.forEach(item => {
                        let row = t.div({class:'item-container fade-in'},()=>{
                            t.div({class:'item-header'},()=>{
                                t.div({class:'row'},()=>{
                                    t.div({class:'col-6'},()=>{
                                        t.txt(item.code);
                                    });
                                    t.div({class:'col-6 text-end'},()=>{
                                        t.button({class:'btn btn-danger'},()=>{
                                            t.i({class:'bi bi-trash-fill'});
                                        }).onclick = (e)=>{
                                            e.stopPropagation();
                                            window.util.confirm('Are you sure you want to remove this role?',(e,res)=>{
                                                if(!res){
                                                    return false;
                                                }

                                                window.util.blockUI();

                                                window.util.$post('/api/role/access_code/remove',{
                                                    role_id: '{{$role->id}}',
                                                    access_code_id: item.id
                                                }).then(reply=>{
                                                    window.util.unblockUI();

                                                    if(reply.status <= 0){
                                                        window.util.showMsg(reply);
                                                        return false;
                                                    }

                                                    if(totalAssignedCount !== null && totalAssignedCount > 0){
                                                        totalAssignedCount--;
                                                    }
                                                    showData((searchAccessCode.value || '').trim(), false);
                                                });
                                            });
                                        };
                                    });
                                });
                            });

                            t.div({class:'item-body'},item.description );
                        });

                        row.onclick = ()=>{
                            window.util.navTo('/access_code/'+item.id+'?b={{url()->current()}}');
                        };
                    });
                });
            });

            $el.append(groupContainer).to(list);
        });

    }

    function showData(query = '', block = true){
        const seq = ++activeSearchSeq;

        if(block){
            window.util.blockUI();
        }

        let params = {
            order_by:'code',
            order:'ASC'
        };

        if(query){
            params.query = query;
        }

        window.util.$get('/api/role/access_codes/{{$role->id}}', params).then(reply=>{
            if(block){
                window.util.unblockUI();
            }

            if(seq !== activeSearchSeq){
                return;
            }

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            let data = reply.data || [];
            data.sort((a, b) => (a.code || '').localeCompare(b.code || ''));

            if(!query || totalAssignedCount === null){
                if(!query){
                    totalAssignedCount = data.length;
                }
            }

            if(totalAssignedCount === 0){
                accessCodeCount.textContent = 'No access codes assigned';
            } else if(query){
                const total = totalAssignedCount !== null ? totalAssignedCount : data.length;
                accessCodeCount.textContent = `Showing ${data.length} of ${total} access codes`;
            } else {
                accessCodeCount.textContent = `Total: ${data.length} access code${data.length === 1 ? '' : 's'}`;
            }

            renderRows(data);
        }).catch(err => {
            if(block){
                window.util.unblockUI();
            }
            console.error(err);
        });
    }

    searchAccessCode.oninput = () => {
        const query = (searchAccessCode.value || '').trim();
        if(query){
            clearSearchBtn.classList.remove('d-none');
        } else {
            clearSearchBtn.classList.add('d-none');
        }
        debouncedSearch(query);
    };

    searchAccessCode.onkeydown = (e) => {
        if(e.key === 'Enter'){
            e.preventDefault();
            debouncedSearch.cancel();
            showData((searchAccessCode.value || '').trim(), false);
        }
    };

    clearSearchBtn.onclick = () => {
        debouncedSearch.cancel();
        searchAccessCode.value = '';
        clearSearchBtn.classList.add('d-none');
        searchAccessCode.focus();
        showData('', false);
    };

    reinitalize();
    showData('', true);

</script>
</div>
@endsection