<div id="content">
    <div class="form-container" id="component_form" >
        <div class="form-header">
            Component
        </div>
        <div class="form-body">
            
          
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Name</label>
                        <input class="form-control editable_field" type="text" id="component" value="{{$component->name}}" disabled="true"/>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Status</label>
                        <input class="form-control" type="text" id="status" value="{{$component->status}}" disabled="true"/>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input class="form-control editable_field" type="text" id="component_quantity" value="{{$component->quantity}}" disabled="true"/>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Unit</label>   
                        <select id="component_unit" class="form-control editable_field" disabled="true">
                            @foreach($unit_options as $opt)
                                <option value="{{$opt->id}}" @if($component->unit_id == $opt->id) selected @endif @if($opt->deleted) disabled="true" @endif>{{$opt->text}} @if($opt->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>         
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                    <label>Use Count</label>
                    <input class="form-control editable_field" type="text" id="use_count" value="{{$component->use_count}}" disabled="true"/>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Sum Flag</label>
                        <div class="form-switch text-center">
                            <input type="checkbox" class="form-check-input editable_field" id="component_sum_flag" value="1"  disabled="true" @if($component->sum_flag == 1) checked @endif/>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-5">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Total Amount</label>
                        <input class="form-control" disabled="true" id="component_total_amount"/>
                    </div>
                </div>
            </div>
           

            <div class="row" id="component_controls">
                <div class="col-lg-6">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                </div>
                <div class="col-lg-6 text-end">

                    @if($component->status == 'PEND')
                        <button class="btn btn-outline-primary" id="reviewLinkBtn">
                            Review Link
                            <i class="bi bi-copy"></i>
                        </button>
                    @endif

                    <button class="btn btn-warning" id="printBtn">Print</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>
</div>