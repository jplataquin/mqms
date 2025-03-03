<div id="content">
    <div class="form-container">
        <div class="form-header">
            Project
        </div>
        <div class="form-body">
            <div class="row">
                <div class="col-lg-12">
                    <table class="w-100 table">
                        <tr>
                            <th>
                                Project Name
                            </th>
                            <td>
                                <input type="text" disabled="true" id="project_name" value="{{$project->name}}" class="form-control"/>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Status
                            </th>
                            <td>
                                <select id="status" disabled="true" class="form-control">
                                    <option value="ACTV" @if($project->status == "ACTV") selected @endif>Active</option>
                                    <option value="INAC" @if($project->status == "INAC") selected @endif>Inactive</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>   

            </div>

            <div class="row mt-5 mb-3">
                <div class="col-lg-12 text-end">
                    <button class="btn btn-danger" id="deleteBtn">Delete</button>
          
                    <button class="btn btn-primary" id="editBtn">Edit</button>
                    <button class="btn btn-warning d-none" id="updateBtn">Update</button>
          
                </div>
            </div>
        </div>
    </div>
</div>