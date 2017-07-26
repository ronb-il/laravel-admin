@extends('layouts/admin')

@section('sidebar-content')
    @include($viewMode . '/sidebar')
@endsection

@section('content')
    <div id="notifier"></div>
    <div class="row" id="operations-tool-buttons">
        <h2>Operations</h2>
        <div class="form-inline">
        @can('add', Resource::get('operations'))
        <button class="btn btn-primary" onclick="addNewOperation(this)" {{$readOnly? "disabled" :""}}>Add New Operation</button>
        @endcan
        @can('publish', Resource::get('operations'))
        <button class="btn btn-primary" onclick="publishChanges()" {{$readOnly? "disabled" :""}}>Publish Changes</button>
        @endcan
        </div>
    </div>
@endsection


@section('custom-javascript')
    <script src="{{ url('scripts/bootstrap-editable.js') }}"></script>
    <script src="{{ url('scripts/operations.js') }}"></script>

    <script id="new-operation" type="text/template">
        <div class="row" style="margin-top:10px">
            <div class="panel panel-default" name="operation-panel">
              <div class="panel-heading clearfix">
                <div class="form-inline pull-left">
	               <button name="status_1" class="btn btn-sm btn-default btn-status" onclick="operationStatusOnChange(this, true)">OFF</button>
                   <button name="status_0" class="btn btn-sm btn-success btn-status hide" onclick="operationStatusOnChange(this, true)">ON</button>
                   <input type="text" class="form-control" name="operation_name" value="" placeholder="Operation Name" onblur="validateOperationName(this)" {{($readOnly||$readOnlyOperationName)? "disabled" : ""}}>
                   <input type="hidden" name="operation_id" value="">
                   <span style="margin-left: 7px; font-size: 16px" class="solution-title"><span><b>Solution:&nbsp;</b></span><span class="solution-name"></span></span>
                </div>
                @can('delete', Resource::get('operations'))
                <button class="btn btn-danger pull-right" onclick="deleteOperation(this)" {{$readOnly? "disabled" :""}}>Delete</button>
                @endcan
              </div>
              <div class="panel-body">
                <div class="col-md-9">
                    <div class="row solution-select" style="display: none">
                        <h5>Solution:</h5>
                        <div class="col-md-4" style="padding:0">
                            <select name="solution" class="form-control" {{$readOnly? "disabled" : ""}}>
                                <option selected="selected" disabled>Select Solution:</option>
                                <option value="0">Conversion Uplift</option>
                                <option value="1">Profit Optimization</option>
                                <option value="2">User Spend Increase</option>
                                <option value="3">Loyalty and Retention</option>
                                <option value="4">Omni-Channel Support</option>
                                <option value="5">PLC Management</option>
                            </select>
                        </div>
                        <div class="col-md-8" style="padding:7px 0 0 7px;color:#EE1473;">Warning: you can't change solution once published</div>
                    </div>
                    <div class="row">
                        <h5>Applied To:</h5>
                        <div class="col-md-4" style="padding:0">
                            <select name="type" class="form-control" onchange="typeOnChange(this)" {{$readOnly? "disabled" : ""}}>
                                <option value="0">All</option>
                                <option value="1" selected>Business Lists</option>
                            </select>
                        </div>
                        <div class="col-md-8" style="padding: 0px 0px 0px 5px; display:none;">
                            <div style="width:100%;padding-bottom:5px;" class="input-group select2-bootstrap-prepend">
                                <div style="width:90px" class="input-group-addon">Included:</div>
                                <select class="form-control select2" multiple="multiple" name="business-lists" tabindex="-1" {{$readOnly? "disabled" :""}}>
                                </select>
                            </div>
                            <div style="width:100%;" class="input-group select2-bootstrap-prepend">
                                <div style="width:90px" class="input-group-addon">Excluded:</div>
                                <select class="form-control select2" multiple="multiple" name="business-lists-excluded" tabindex="-1" {{$readOnly? "disabled" :""}}>
                                </select>
                            </div>
                        </div>
                    </div>

                  <div class="row" style="padding-top:10px;">
                      @can('view', Resource::get('operations-sg'))
                      <button class="btn btn-primary" data-toggle="collapse" data-target="#sample-group">Show Sample Groups</button>
                      @endcan
                      <div name="sample-groups-container" class="collapse" style="margin-top:10px">
                        <table name="sample-group" class="table table-condensed table-bordered">
                              <thead>
                                <tr>
                                  <th class="col-md-4">Sample Group ID</th>
                                  <th class="col-md-4">Sample Group Description</th>
                                  <th class="col-md-2">Size (%)</th>
                                  <th class="col-md-1">Is Control Group?</th>
                                  <th class="col-md-1">
                                      <!-- <button title="Add New Sample Group" onclick="addEditableSampleGroupRow(this, true)" type="button" class="btn btn-success btn-sm"><span class="fa fa-plus"></span></button> -->
                                      @can('edit', Resource::get('operations-sg'))
                                      <button name="save-all" title="Save Changes" type="button" class="btn btn-success" onclick="saveAllSampleGroups(this)" {{$readOnly? "disabled" :""}}>Save</button>
                                      <button name="edit-all" title="Edit Sample Groups" type="button" class="btn btn-primary" onclick="editAllSampleGroups(this)" {{$readOnly? "disabled" :""}}>Edit</button>
                                      <input type="hidden" name="sample-group-data" value="" />
                                      @endcan
                                  </th>
                                </tr>
                              </thead>
                              <tbody>
                              </tbody>
                         </table>
                       </div>
                   </div>
                  </div>
              </div>
            </div>
        </div>
    </script>

    <script id="editable-sample-group-template" type="text/template">
      <tr>
        <td><input type="text" name="sg_id" class="form-control" original-value=""></td>
        <td><input type="text" name="sg_description" class="form-control" original-value=""></td>
        <td><input type="text" name="sg_size" _onkeyup="adjustSize(this)" class="form-control" original-value=""></td>
        <td align="center" style="vertical-align:middle;">
            <input type="checkbox" />
        </td>
        <td nowrap>
            <!--
            <button class="btn btn-primary" onclick="save(this)">Save</button>
            <button class="btn btn-primary" onclick="cancel(this)">Cancel</button>
            -->
            <input type="hidden" name="sg_is" value="">
            <input type="hidden" name="sg_id" value="">
        </td>
      </tr>
    </script>

    <script id="sample-group-template" type="text/template">
          <tr>
            <td name="sg_id" style="vertical-align:middle"></td>
            <td name="sg_description" style="vertical-align:middle"></td>
            <td name="sg_size" style="vertical-align:middle" align="center" original-value=""></td>
            <td name="sg_is_control" style="vertical-align:middle"></td>
            <td nowrap>
                <!--
                <button class="btn" onclick="edit(this)">Edit</button>
                <button class="btn" onclick="remove(this)">Delete</button>
                -->
            </td>
          </tr>
    </script>

    <script type="text/javascript">
        var publishChangesUrl = "{{ url('rules/rule-set/' . $rule_set_id . '/operations') }}";
        var businessLists = JSON.parse('{!! $businessLists !!}');

        (function(){
            var operations = JSON.parse('{!! $operations_json !!}');

            for(index in operations.operations) {
                addExistingOperation(operations.operations[index]);
            }
        })();

        window.addEventListener('sessionchanged', function (e) {
            window.location.href = '{{ url('/rules/') }}';
        });
    </script>

@endsection
