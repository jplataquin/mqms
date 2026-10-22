@extends('layouts.app')

@section('content')
<div id="content">
<div class="container-fluid px-4 py-3">
    <div class="breadcrumbs mb-3" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul class="list-inline mb-0">
            <li class="list-inline-item">
                <a href="/master_data/material/items">
                    <span>Material Items</span>
                </a>
            </li>
            <li class="list-inline-item">/</li>
            <li class="list-inline-item active">
                <span>Maintenance</span>
                <i class="ms-1 bi bi-tools"></i>
            </li>
        </ul>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1">Material Items Maintenance</h2>
            <div class="text-muted small">Manage and inline-edit all material items and their material groups.</div>
        </div>
        <div>
            <a href="/master_data/material/items/print" target="_blank" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-printer"></i> Print View
            </a>
            <a href="/master_data/material/items" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-ul"></i> Standard List
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="/master_data/material/items/maintenance" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="query" class="form-control" placeholder="Search by name, brand, spec..." value="{{ $query }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Search</button>
                    @if(!empty($query))
                        <a href="/master_data/material/items/maintenance" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content: Grouped Items -->
    @if(count($groupedMaterialItems ?? []) > 0)
        @foreach($groupedMaterialItems as $group)
            <div class="card mb-4 border shadow-sm group-section">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background-color: var(--bs-tertiary-bg, #f8fafc);">
                    <div class="fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-folder2-open me-2 text-primary"></i>{{ $group['group_name'] }}
                    </div>
                    <span class="badge bg-secondary rounded-pill">
                        {{ count($group['items']) }} {{ count($group['items']) == 1 ? 'item' : 'items' }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle group-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 4%;" class="text-center">#</th>
                                <th style="width: 6%;" class="text-center">ID</th>
                                <th style="width: 18%;">Material Group</th>
                                <th style="width: 28%;">Material Name</th>
                                <th style="width: 22%;">Specification / Packaging</th>
                                <th style="width: 12%;">Brand</th>
                                <th style="width: 10%;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['items'] as $index => $item)
                                <tr id="row-{{ $item->id }}" data-id="{{ $item->id }}" data-group-id="{{ $item->material_group_id ?? 0 }}">
                                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                                    <td class="text-center font-monospace">{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <span class="view-mode view-group">{{ $item->Group ? $item->Group->name : 'Ungrouped' }}</span>
                                        <select class="form-select form-select-sm edit-mode edit-group d-none" required>
                                            <option value="0" {{ empty($item->material_group_id) ? 'selected' : '' }}>Ungrouped</option>
                                            @foreach($allMaterialGroups ?? [] as $mg)
                                                <option value="{{ $mg->id }}" {{ ($item->material_group_id == $mg->id) ? 'selected' : '' }}>{{ $mg->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <span class="view-mode view-name">{{ $item->name }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode edit-name d-none" value="{{ $item->name }}" required>
                                    </td>
                                    <td>
                                        <span class="view-mode view-spec">{{ $item->specification_unit_packaging }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode edit-spec d-none" value="{{ $item->specification_unit_packaging }}" required>
                                    </td>
                                    <td>
                                        <span class="view-mode view-brand">{{ $item->brand ?: '-' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode edit-brand d-none" value="{{ $item->brand }}">
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary btn-edit" title="Edit Item">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-success btn-save d-none" title="Save Changes">
                                                <i class="bi bi-check-lg"></i> Save
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-cancel d-none" title="Cancel">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="card p-5 text-center text-muted shadow-sm border-0">
            <i class="bi bi-inbox fs-1 mb-2"></i>
            <h5>No material items found.</h5>
            <p class="small mb-0">Try adjusting your search criteria.</p>
        </div>
    @endif

    @if(isset($paginator) && $paginator->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
            <div class="text-muted small">
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} items
            </div>
            <div>
                {{ $paginator->links() }}
            </div>
        </div>
    @endif
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        // Event delegation for Edit, Cancel, Save
        document.body.addEventListener('click', (e) => {
            const row = e.target.closest('tr[data-id]');
            if (!row) return;

            // Click Edit
            if (e.target.closest('.btn-edit')) {
                toggleRowEdit(row, true);
            }

            // Click Cancel
            if (e.target.closest('.btn-cancel')) {
                resetRowValues(row);
                toggleRowEdit(row, false);
            }

            // Click Save
            if (e.target.closest('.btn-save')) {
                saveRow(row);
            }
        });

        function toggleRowEdit(row, isEditing) {
            const viewEls = row.querySelectorAll('.view-mode');
            const editEls = row.querySelectorAll('.edit-mode');
            const editBtn = row.querySelector('.btn-edit');
            const saveBtn = row.querySelector('.btn-save');
            const cancelBtn = row.querySelector('.btn-cancel');

            if (isEditing) {
                viewEls.forEach(el => el.classList.add('d-none'));
                editEls.forEach(el => el.classList.remove('d-none'));
                editBtn.classList.add('d-none');
                saveBtn.classList.remove('d-none');
                cancelBtn.classList.remove('d-none');
                
                // Focus first input (group select or name)
                const groupSelect = row.querySelector('.edit-group');
                if (groupSelect) groupSelect.focus();
            } else {
                viewEls.forEach(el => el.classList.remove('d-none'));
                editEls.forEach(el => el.classList.add('d-none'));
                editBtn.classList.remove('d-none');
                saveBtn.classList.add('d-none');
                cancelBtn.classList.add('d-none');
            }
        }

        function resetRowValues(row) {
            const groupSelect = row.querySelector('.edit-group');
            const nameInput = row.querySelector('.edit-name');
            const specInput = row.querySelector('.edit-spec');
            const brandInput = row.querySelector('.edit-brand');

            const currentGroupId = row.getAttribute('data-group-id') || '0';
            if (groupSelect) groupSelect.value = currentGroupId;

            nameInput.value = row.querySelector('.view-name').innerText.trim();
            specInput.value = row.querySelector('.view-spec').innerText.trim();
            const brandText = row.querySelector('.view-brand').innerText.trim();
            brandInput.value = brandText === '-' ? '' : brandText;
        }

        function saveRow(row) {
            const id = row.getAttribute('data-id');
            const originalGroupId = row.getAttribute('data-group-id') || '0';
            const groupSelect = row.querySelector('.edit-group');
            const nameInput = row.querySelector('.edit-name');
            const specInput = row.querySelector('.edit-spec');
            const brandInput = row.querySelector('.edit-brand');

            const selectedGroupId = groupSelect ? groupSelect.value : originalGroupId;
            const name = nameInput.value.trim();
            const spec = specInput.value.trim();
            const brand = brandInput.value.trim();

            if (!name) {
                alert('Material Name is required.');
                nameInput.focus();
                return;
            }

            if (!spec) {
                alert('Specification / Unit Packaging is required.');
                specInput.focus();
                return;
            }

            if (window.util && window.util.blockUI) {
                window.util.blockUI();
            }

            window.util.$post('/api/material/item/update', {
                id: id,
                material_group_id: selectedGroupId,
                name: name,
                specification_unit_packaging: spec,
                brand: brand
            }).then(reply => {
                if (window.util && window.util.unblockUI) {
                    window.util.unblockUI();
                }

                if (reply.status <= 0) {
                    if (window.util && window.util.showMsg) {
                        window.util.showMsg(reply);
                    } else {
                        alert(reply.message || 'Error updating material item.');
                    }
                    return;
                }

                // If material group changed, reload so item moves to its new group section
                if (String(selectedGroupId) !== String(originalGroupId)) {
                    if (window.util && window.util.navReload) {
                        window.util.navReload();
                    } else {
                        window.location.reload();
                    }
                    return;
                }

                // If group is the same, update text view elements in place
                const groupText = groupSelect.options[groupSelect.selectedIndex].text;
                row.querySelector('.view-group').innerText = groupText;
                row.querySelector('.view-name').innerText = name;
                row.querySelector('.view-spec').innerText = spec;
                row.querySelector('.view-brand').innerText = brand ? brand : '-';

                // Return to view mode
                toggleRowEdit(row, false);
            }).catch(err => {
                if (window.util && window.util.unblockUI) {
                    window.util.unblockUI();
                }
                alert('A network error occurred while updating.');
            });
        }
    });
</script>
</div>
@endsection
