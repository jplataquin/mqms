<div id="content">
    <div class="form-container">
        <div class="form-header">
            Section
        </div>
        <div class="form-body">
            <div class="row">
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

</div>