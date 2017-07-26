@extends('layouts/admin')

@section('sidebar-content')
    @include('behaviorrules/sidebar')
@endsection

@section('content')
        <div class="box round first">

        </div>
        <div class="box grid" id="rule-set-list-wrapper">
            <div class="message error" id="error-container" style="display:none">
                <p style="color:red"></p>
            </div>
            <h2>Rule Sets</h2>
            <div class="box" id="rule-set-list-tbl-wrapper">
                <table class="table table-hover table-striped table-bordered dataTables_wrapper form-inline dt-bootstrap no-footer" id="rule-set-list-tbl">
                    <thead>
                        <tr id="0" class="rule-set-list">
                            <th>Active</th>
                            <th>Rule Set</th>
                            <th>Description</th>
                            <th>Start date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr class="odd gradeX" id="add-new-rule-set">
                        <td>
                        </td>
                        <td class="rule-set-name">
                            <input type="text" class="rule-set-name form-control" maxlength="100">
                        </td>
                        <td class="rule-set-notes">
                            <input type="text" class="rule-set-notes form-control" maxlength="250">
                        </td>
                        <td class="rule-set-start-date">
                        </td>
                        <td class="actions">
                            <a class="form-control" href='javascript:addNewRuleSet()'>Add</a>
                        </td>
                    </tr>
                    @foreach ($rulesSet['ruleSetList'] as $ruleSet=>$rule)
                        <tr class="odd gradeX rule-set" id="rule-set-{{ $ruleSet }}">
                            <td>
                                {!! Form::radio('active-rule-button', $ruleSet, ($ruleSet == $rulesSet['activeRuleSet'] ? true : false), ['class' => 'active-rule-button form-control']) !!}
                            </td>
                            <td class="rule-set-name">
                                <input class="form-control" type="text" disabled="disabled" value="{{ $rule['name']  }}"></input>
                            </td>
                            <td class="rule-set-notes">
                                <input class="form-control" type="text" disabled="disabled" value="{{ $rule['notes'] }}"></input>
                            </td>
                            <td class="rule-set-start-date">
                                {{ $rule['start_date'] != '' ? date('m/d/y', strtotime($rule['start_date'])) : '' }}
                            </td>
                            <td class="actions" nowrap>
                                <div class='actions-float-right'>
                                    <a class='form-control' href='/rules/rule-set/affiliate/{{ $affiliateId }}/set/{{ $ruleSet }}/prod/prod' class='rule-set-edit-rules'>Edit</a>
                                    <a class='form-control' href='/rules/rule-set/{{ $ruleSet }}/operations'>Operations</a>
                                    @can('edit', Resource::get('rules'))
                                        <a class='form-control' href='javascript:cloneRuleSet({{ $ruleSet }})'>Clone</a>
                                        <a class='form-control rule-set-save btn-danger' href='javascript:saveRuleSetSettings({{ $ruleSet }})' style='display:none'>Save</a>
                                        <a class='form-control rule-set-settings' href='javascript:editRuleSetSettings({{ $ruleSet }})'>Settings</a>
                                        <a class='form-control rule-set-delete btn-danger' href='javascript:deleteRuleSet({{ $ruleSet }})'>Delete</a>
                                    @endcan
                                </div>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

<br>
<br>
<hr>
<br>
        <!--  History start -->
        <div class="box" id="rule-set-list-history-wrapper">
            <h2>History</h2>
            <div class="box" id="rule-set-list-history-tbl-wrapper">
                <table class="table table-hover dataTables_wrapper form-inline dt-bootstrap no-footer" id="rule-set-list-history-tbl">
                    <thead>
                    <tr class="rule-set-list-filter-panel">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr class="rule-set-list">
                        <th>Rule Set</th>
                        <th>Description</th>
                        <th>Start date</th>
                        <th>End date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($historyRuleSet['ruleSetList'] as $ruleSet=>$rule)
                        <tr class="odd gradeX rule-set" id="rule-set-{{ $ruleSet }}">
                            <td class="rule-set-name">
                                {{ $rule['name']  }}
                            </td>
                            <td class="rule-set-notes">
                                {{ $rule['notes'] }}
                            </td>
                            <td class="rule-set-start-date">
                                {{ $rule['start_date'] != '' ? date('m/d/y', strtotime($rule['start_date'])) : '' }}
                            </td>
                            <td class="rule-set-end-date">
                                {{ $rule['end_date'] != '' ? date('m/d/y', strtotime($rule['end_date'])) : '' }}
                            </td>
                            <td class="actions" nowrap>
                                <div class='actions-float-right'>
                                    <a href='/rules/rule-set/affiliate/{{ $historyRuleSet['affiliateId'] }}/set/{{ $ruleSet }}/prod/prod/history' class='rule-set-edit-rules'>Rules</a> |
                                    <a href='/rules/rule-set/{{$ruleSet}}/operations/read_only'>Operations</a> |
                                    <a href='javascript:cloneRuleSet({{ $ruleSet }})'>Clone</a> 
                                </div>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!--  History end -->
@endsection

@section('custom-javascript')
    <script type="text/javascript">
        window.addEventListener('sessionchanged', function (e) {
            window.location.href = '{{ url('/rules/') }}';
        });

            $(document).ready(function () {
                $('#rule-set-list-tbl #rule-set-58 .active-rule-button').prop("checked", "checked");
                if($('#rule-set-list-tbl #rule-set-58 b.active-rule-button').length > 0)
                {
                    $('#rule-set-list-tbl #rule-set-58').css('background-color','#86ff86');
                }
                $('#rule-set-list-tbl #rule-set-58').addClass("active-rule-set");


                $('.active-rule-button').change(function(btn){
                    var ruleSetId = $(btn)[0].currentTarget.value;
                    var ruleSetName = $('#rule-set-list-tbl #rule-set-'+ruleSetId +  ' .rule-set-name input').val();
                    if(!confirm("Are you sure you want to activate rule set \n \"" + $.trim(ruleSetName) + "\" ?")){
                        location.reload();
                        return;
                    }

                    $.ajax({
                        url: "/rules/rule-set/set-active-rule-set/affiliate/{{ $affiliateId }}/set/" + ruleSetId ,
                        dataType: "json",
                        success: function(data){
                            if(data.status == "true")
                                location.reload();
                            else
                                displayError(data.message);
                        },
                        error: function(){
                            displayError("fail to set active rule set. is web sever up?");
                        }
                    });
                });
            });
            $(function(){
                addNewRuleSet = function(){
                    var newRuleSet = {};
                    newRuleSet.affiliate = '{{ $affiliateId }}';
                    newRuleSet.ruleSetName = $('#rule-set-list-tbl #add-new-rule-set .rule-set-name input').val();
                    newRuleSet.ruleSetNotes = $('#rule-set-list-tbl #add-new-rule-set .rule-set-notes input').val();

                    if(newRuleSet.ruleSetName == "" || newRuleSet.ruleSetNotes == ""){
                        displayError("Name and description must be supplied.");
                        $('#rule-set-list-tbl #add-new-rule-set .rule-set-name input').focus();
                        return;
                    }

                    $.ajax({
                        url: "/rules/rule-set/add",
                        type: "GET",
                        dataType: "json",
                        data: $.param(newRuleSet),
                        success: function(data){
                            if(data.status == "true")
                                location.reload();
                            else
                                displayError(data.message);
                        },
                        error: function(){
                            displayError("fail to add new rule set. is web sever up?");
                        }
                    });
                };

                cloneRuleSet = function(ruleSetId){
                    var affiliate_id = '{{ $affiliateId }}';
                    var params = {
                        'affiliate_id' : affiliate_id,
                        'rule_set_id' : ruleSetId,
                        '_token' : '{{ csrf_token() }}'
                    }
                    $.ajax({
                        url: '/rules/rule-set/clone',
                        type: "GET",
                        data: params,
                        dataType: "json",
                        success: function(data){
                            if(data.status == "true")
                                location.reload();
                            else
                                displayError(data.message);
                        },
                        error: function(){
                            displayError("fail to clone rule set. is web sever up?");
                        }
                    });
                };

                editRuleSetSettings = function(ruleSetId){
                    var ruleSet = $('#rule-set-list-tbl #rule-set-' + ruleSetId);

                    ruleSet.addClass('current-edit-rule-set');
                    ruleSet.find('.rule-set-notes input').prop('disabled', '');
                    ruleSet.find('.rule-set-name input').prop('disabled', '');
                    ruleSet.find('.rule-set-name input').focus();

                    var actions = $('#rule-set-list-tbl #rule-set-' + ruleSetId).find('.actions');
                    actions.find('.rule-set-save').show();
                    actions.find('.rule-set-settings').hide();

                };
                deleteRuleSet = function(ruleSetId) {
                    var affiliate_id = '{{ $affiliateId }}';
                    var params = {
                        'affiliate_id' : affiliate_id,
                        'rule_set_id' : ruleSetId,
                        '_token' : '{{ csrf_token() }}'
                    }
                    var delete_rule_set = function() {
                        $.ajax({
                            url: "/rules/rule-set/delete",
                            type: "POST",
                            dataType: "json",
                            data: params,
                            success: function(data)
                            {
                                window.top.location.href = window.top.location.href;
                                if(data.status == "true")
                                {
                                    window.top.location.href = window.top.location.href;
                                }

                            },
                            error: function()
                            {
                                displayError("fail to delete rule set. make sure it's not active");
                            }
                        });
                    };

                    if(confirm("Are you sure you want to delete this ruleset?\n (Active ruleset will not be deleted)")){
                        delete_rule_set();
                    }
                }
                saveRuleSetSettings = function(ruleSetId){
                    var ruleSet = $('#rule-set-list-tbl #rule-set-' + ruleSetId);

                    var close = function(){
                        ruleSet.find('.rule-set-settings').show();
                        ruleSet.find('.rule-set-save').hide();

                        ruleSet.removeClass('current-edit-rule-set');
                        ruleSet.find('.rule-set-notes input').prop('disabled', 'disabled');
                        ruleSet.find('.rule-set-name input').prop('disabled', 'disabled');
                    };

                    var ruleSetEdit = {};
                    ruleSetEdit.affiliate_id = '{{ $affiliateId }}';
                    ruleSetEdit.rule_set_id = ruleSetId;
                    ruleSetEdit.ruleSetName = ruleSet.find('.rule-set-name input').val();
                    ruleSetEdit.ruleSetNotes = ruleSet.find('.rule-set-notes input').val();
                    ruleSetEdit._token = '{{ csrf_token() }}';

                    if(ruleSetEdit.ruleSetName == "" || ruleSetEdit.ruleSetNotes == ""){
                        displayError("Name and description must be supplied.");
                        ruleSet.find('.rule-set-name input').focus();
                        return;
                    }

                    $.ajax({
                        url: "/rules/rule-set/editSettings",
                        type: "GET",
                        dataType: "json",
                        data: $.param(ruleSetEdit),
                        success: function(data){
                            if(data.status == 'true'){
                                close();
                            }
                            else
                                displayError(data.message);
                        },
                        error: function(){
                            displayError("fail to edit rule set. is web sever up?");
                        }
                    });


                };
            });
        </script>

        <script type="text/javascript">
            $(function() {
                displayError = function(msg){
                    var errorContainer = $('#error-container');
                    $(document).scrollTop(errorContainer.offset().top);
                    errorContainer.find('p').text(msg);
                    errorContainer.fadeIn();
                    errorContainer.fadeOut();
                    errorContainer.fadeIn();
                };
                resetError = function(){
                    var errorContainer = $('#error-container');
                    errorContainer.find('p').text('');
                    errorContainer.fadeOut();
                };

            });
        </script>

        <script type="text/javascript">
            console.warn('show.blade.php: Fix datepicker in the rules history, switch to bootstrap datepicker');
            $(document).ready(function () {
                $('#rule-set-list-history-tbl').dataTable( {
                    "sorting" : true,
                    "paging": true,
                    "order": [[ 2, "desc" ]],
                    "autoWidth": false
                } );

                //$.datepicker.regional[""].dateFormat = 'mm/dd/yy';

                /*$('#rule-set-list-history-tbl').dataTable({
                    aoColumns:[
                        null,
                        null,
                        {'sWidth': '200px'},
                        {'sWidth': '200px'},
                        null
                    ]
                }).
                columnFilter({
                    sPlaceHolder: "head:before",
                    aoColumns: [null,
                        null, {
                            type: "date-range"
                        }, {
                            type: "date-range"
                        },
                        null
                    ]
                });*/
            });


        </script>

    <script src="{{ url('scripts/datatables.js') }}" type="text/javascript"></script>
    <script src="{{ url('scripts/dataTables.bootstrap.js') }}" type="text/javascript"></script>

@endsection
