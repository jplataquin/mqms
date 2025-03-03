<div id="content">
    <div class="form-container">
        <div class="form-header text-center mb-3">
            Contract Item
        </div>
        <div class="form-body">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Item Code</label>
                        <input type="text" id="item_code" class="form-control editable" disabled="true" value="{{$contract_item->item_code}}"/>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label>ID</label>
                    <input type="text" class="form-control" disabled="true" value="{{STR_PAD($contract_item->id,6,0,STR_PAD_LEFT)}}"/>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" id="description" class="form-control editable" disabled="true" value="{{$contract_item->description}}"/>
                    </div>
                </div>
            </div>
            
            
            <div class="row mb-3">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Contract Quantity</label>
                        <input type="text" id="contract_quantity" class="form-control editable" disabled="true" value="{{$contract_item->contract_quantity}}"/>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Contract Unit</label>
                        <select class="form-control editable" id="unit" disabled="true">
                            @foreach($unit_options as $unit)
                            <option value="{{$unit->id}}" 
                                @if($unit->deleted) disabled @endif
                            
                                @if($unit->id == $contract_item->unit_id) selected @endif
                            
                            >{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Contract Unit Price (PHP)</label>
                        <input type="text" id="contract_unit_price" class="form-control editable" disabled="true" value="{{$contract_item->contract_unit_price}}"/>
                    </div>
                </div>
            </div>

            <div class="row mb-3 ">


                <div class="col-lg-4">
                    <div class="form-group">
                        <label>POW/DUPA Quantity</label>
                        <input type="text" id="ref_1_quantity" class="form-control editable" disabled="true" value="{{$contract_item->ref_1_quantity}}"/>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>POW/DUPA Unit</label>
                        <select class="form-control editable" id="ref_1_unit" disabled="true">
                            <option value=""> - </option>
                            @foreach($unit_options as $unit)
                            
                            <option value="{{$unit->id}}" 
                                @if($unit->deleted) disabled @endif
                            
                                @if($unit->id == $contract_item->ref_1_unit_id) selected @endif
                            
                            >{{$unit->text}} @if($unit->deleted) [Deleted] @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>POW/DUPA Unit Price (PHP)</label>
                        <input type="text" id="ref_1_unit_price" class="form-control editable" disabled="true" value="{{$contract_item->ref_1_unit_price}}"/>
                    </div>
                </div>
                
            </div>
            
            

            <div class="row mb-3">
                <div class="col-lg-12 text-end">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                  
                </div>
            </div>
        </div>
    </div>
</div>