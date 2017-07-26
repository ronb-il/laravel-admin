@extends('layouts/admin')

@section('sidebar-content')
    @include('behaviorrules/sidebar')
@endsection

@section('content')

@foreach($ruleSet['configuration']['userFacts'] as $user_fact)
    @foreach($user_fact as $conditionId => $conditionValue)
        {!! App\Helpers\HtmlElement::fromJSON($conditionId, $conditionValue) !!}
    @endforeach
@endforeach

{{--//conditions html elements templates--}}
@foreach($ruleSet['configuration']['conditions'] as $conditionId=>$conditionValue)
    {!!  App\Helpers\HtmlElement::fromJSON($conditionId, $conditionValue) !!}
@endforeach

{{--//actions html elements templates--}}
@foreach($ruleSet['configuration']['actions'] as $actionId=>$actionValue)
    {!! App\Helpers\HtmlElement::fromJSON($actionId, $actionValue) !!}
@endforeach

{{--//simulator conditions html elements templates--}}
@foreach($ruleSet['configuration']['responseConditions'] as $conditionId=>$conditionValue)
    {!! App\Helpers\HtmlElement::fromJSON($conditionId, $conditionValue) !!}
@endforeach

{{--//state html element template--}}
{!! App\Helpers\HtmlElement::fromJSON('state', $ruleSet['configuration']['state']) !!}

{!! App\Helpers\HtmlElement::fromJSON('productType', $ruleSet['configuration']['productType']) !!}


<!-- RULE SET TABLE START -->
    <script type="text/javascript">
    $(function(){
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

        bindUnSavedChangesAlert = function(){
            $(window).bind('beforeunload',function(){
                return 'There are unpublished changes. are you sure you want to leave?';
            });
        };

        unBindUnSavedChangesAlert = function(){
            $(window).unbind('beforeunload');
        };

        loadTableDnd = function(){
            //console.warn('SKIPPING DRAG AND  DROP - NOT SUPPORTED');
            //return;

             $('#rule-set-tbl').tableDnD({
                onDragClass: "on-drag",
                onDrop: function(table, row){
                    var ruleId =  $(row).children().find("#rule-id").val();

                    if(row.rowIndex <= oldIndex){
                        for(var i=row.rowIndex; i<oldIndex; i++){
                            $(table.rows[i]).children().find("#rule-priority").val($(table.rows[i+1]).children().find("#rule-priority").val());
                        }

                        $(table.rows[oldIndex]).children().find("#rule-priority").val(oldPriority);
                    }
                    else{
                        for(var i=row.rowIndex; i>oldIndex; i--){
                            $(table.rows[i]).children().find("#rule-priority").val($(table.rows[i-1]).children().find("#rule-priority").val());
                        }

                        $(table.rows[oldIndex]).children().find("#rule-priority").val(oldPriority);
                    }

                    var _data = {};
                    _data.affiliateId  = {{$affiliate['id']}};
                    _data.ruleSetId =  {{$ruleSet['id']}};
                    _data.priorities = {};
                    $('#rule-set-tbl tr').each(function(index, value){
                        _data.priorities[$(value).find('#rule-id').val()] = $(value).find('#rule-priority').val();
                    });

                    $.ajax({
                        url: "/rules/rule-set/set-rule-priority" ,
                        dataType: "json",
                        type: "POST",
                        data: {data: JSON.stringify(_data), _token : '{{csrf_token()}}'},
                        success: function(data){
                            if(data.status == "true")
                                refresh('{{$ruleId}}');
                            else
                                displayError(data.message);
                        },
                        error: function(){
                            displayError("Fail to switch priorities. is web sever up?");
                        }});


                },
                onDragStart: function(table, row){
                    oldIndex = row.rowIndex;
                    oldPriority = $(row).children().find("#rule-priority").val();
                }
            });
        };

        refresh = function(ruleId, hideOffs, rulesByType){
            var href = "{{ App\Helpers\EnvironmentUtilities::getSiteURL()}}/rules/rule-set/affiliate/{{$affiliate['id']}}/set/{{$ruleSet['id'] }}";

            if(rulesByType)
                href+=  "/product-type/" + rulesByType;
            else
                href+=  "/product-type/{{$productType}}"; //leave current filter

            if(hideOffs)
                href+=  "/off/" + hideOffs;
            else
                href+=  "/off/{{$hideOffs}}"; //leave current filter

            if(ruleId && ruleId != '-1')
                href+= "/rule/" + ruleId;

            if(mode == "history")
            {
                href+= "/" + mode;
            }

            unBindUnSavedChangesAlert();

            var target = href + window.location.hash;
            if(target == window.location.href)
                window.location.reload();
            else
                window.location.href = target;
        };

        removeElement = function(element){
            $(element).remove();
        };

        addConditionCriteria = function(_ruleId, criteria, _value){
            var current_product_type = getCurrentProductType();
            if($('#template-'+current_product_type+'-'+criteria).length == 1)
            {
                criteria = current_product_type+'-'+criteria;
                addCriteria(_ruleId, criteria, _value, 'conditions-board');
                normalizeConditionByProductType(criteria);

            }
            else
            {
                addCriteria(_ruleId, criteria, _value, 'conditions-board');
            }


        };
        addUserFactsCriteria = function(_ruleId, criteria, _value,namespace) {

            addCriteria(_ruleId, criteria, _value, namespace + '-board');

        }

        getCurrentProductType = function() {
            return $('#productType').val()=='generic'?'product':$('#productType').val();
        }
        getProductTypes = function() {
            return $.map($('#productType option') ,function(option) {
                return option.value;
            });
        }
        normalizeConditionByProductType = function(id) {

            var current_product_type = getCurrentProductType();
            var condition_split = id.split('-');
            if(condition_split.length == 2 && condition_split[0] == current_product_type)
            {
                $('#'+id)
                        .attr('id',id.replace(current_product_type+'-',''))
                        .removeClass(id)
                        .addClass(condition_split[1]);
            }
        }
        normalizeConditionByAllProductTypes = function(id,context) {

            var product_types = ['cart','product'];
            var condition_split = id.split('-');
            var product_type_index = $.inArray(condition_split[0],product_types);
            if(condition_split.length == 2 && product_type_index > -1)
            {
                var obj = $('#'+id);
                if(context) obj =$(context).find('#'+id);
                obj
                        .attr('id',id.replace(product_types[product_type_index]+'-',''))
                        .removeClass(id)
                        .addClass(condition_split[1]);
            }
        }

        addActionCriteria = function(_ruleId, criteria, _value){
            addCriteria(_ruleId, criteria, _value, 'actions-board');
        };

        addStateCriteria = function(_ruleId, _value){
            addCriteria(_ruleId, 'state', _value, 'state-board');
        };

        addProductTypeCriteria = function(_ruleId, _value){
            addCriteria(_ruleId, 'productType', _value, 'product-type-board');
        };

        addCriteria = function(_ruleId, criteria, _value, containerId, noFocusAfterAppend){
            var container = $('#rule-' + _ruleId + ' .' + containerId);
            var template = $('#template-' + criteria);
            if(container.find('.' + criteria).length > 0){
                displayError(criteria + ' already exists.');
                return;
            }

            $(container).append(_.template(template.html(),{ruleId: _ruleId , value: _value}));
            if (!noFocusAfterAppend) $(container).find('.' + criteria).focus();
        };

        addConditionsCriteriaPanel = function(_ruleId){
            var template = $('#template-conditions-criteria-panel');
            var container = $('#rule-' + _ruleId + ' .conditions-criteria-panel-wrapper');
            $(container).append(_.template(template.html(),{ruleId: _ruleId}));
        };

        addActionsCriteriaPanel = function(_ruleId){
            var template = $('#template-actions-criteria-panel');
            var container = $('#rule-' + _ruleId + ' .actions-criteria-panel-wrapper');
            $(container).append(_.template(template.html(),{ruleId: _ruleId}));
        };

        editRule = function(_ruleId){
            refresh(_ruleId);
        };

        deleteRule = function(_ruleId){
            var _data={};
            _data.affiliateId= '{{$affiliate['id']}}';
            _data.ruleSetId = '{{$ruleSet['id']}}';
            _data.ruleId = _ruleId;

            var _isDelete = confirm("Are you sure you want to delete ruleId: [" + _ruleId + "] ?");
            if( _isDelete == true ){
                $.ajax({
                    url: "/rules/rule-set/rule/delete-rule" ,
                    dataType: "json",
                    data: $.param(_data),
                    success: function(data){
                        if(data.status =="true"){
                            refresh();
                        }
                        else{
                            displayError(data.message);
                        }
                    },
                    error: function(){
                        displayError("Fail to delete rule: is web server up?");
                    }});
            }
            else{
                return;
            }
        };

        saveRule = function(_ruleId){
            var _data={};
            _data.affiliateId= '{{$ruleSet['affiliateId']}}';
            _data.ruleSetId = '{{$ruleSet['id']}}';

            _data.rule={};
            _data.rule.id = _ruleId;
            _rule = _data.rule;
            _rule.name = $('#rule-' + _ruleId + ' #rule-name').val();
            _rule.productType = {};
            _rule.priority = $('#rule-' + _ruleId + ' #rule-priority').val();
            _rule.conditions={};
            _rule.userFacts={};
            _rule.actions={};
            _rule.state={};
            _rule.domain=$('#rule-' + _ruleId).data('rule-domain');
            _rule.operation=$('#rule-' + _ruleId).data('rule-operation');

            $.each($('#rule-' + _ruleId + ' .conditions-board').find('input, select, textarea'), function(index, element){
                _rule.conditions[element.id] = element.value;
            });

            $('#rule-' + _ruleId + ' .userfacts-board .board').each(function() {

                var userfacts_obj = $(this);
                var namespace = userfacts_obj.attr('namespace');
                _rule.userFacts[namespace] = {};

                $.each(userfacts_obj.find('input, select, textarea'), function(index, element){
                    _rule.userFacts[namespace][element.id] = element.value;
                });

            });
            _rule.userFacts = JSON.stringify(_rule.userFacts);

            //collect actions
            $.each($('#rule-' + _ruleId + ' .actions-board').find('input, select, textarea'), function(index, element){
                _rule.actions[element.id] = element.value;
            });

            //collect state
            $.each($('#rule-' + _ruleId + ' .state-board').find('input, select, textarea'), function(index, element){
                _rule.state[element.id] = element.value;
            });
            //collect productType
            $.each($('#rule-' + _ruleId + ' .product-type-board').find('input, select, textarea'), function(index, element){
                _rule.productType[element.id] = element.value;
            });
            $.ajax({
                url: "/rules/rule-set/rule/update-rule" ,
                data: {data: JSON.stringify(_data), _token : '{{csrf_token()}}'},
                type: "POST",
                dataType: "json",
                success: function(data){
                    if(data.status =="true"){
                        if(data.modified == "true")
                            window.location.hash = 'dirty';
                        refresh();
                    }
                    else{
                        displayError(data.message);
                    }
                },
                error: function(){
                    displayError("Fail to edit rule: is web sever up?");
                }});
        };

        addNewRule = function(){
            $.ajax({
                url: "/rules/rule-set/add/affiliate/{{$ruleSet['affiliateId']}}/set/{{$ruleSet['id']}}" ,
                type: "GET",
                dataType: "json",
                success: function(data){
                    if(data.status == 'true')
                        refresh();
                    else
                        displayError(data.message);
                },
                error:function(){
                    displayError("Fail to add new rule: is web sever up?");
                }});
        };

        testAndPublish = function(){
            var url = "/rules/rule-set/publish-confirm/affiliate/{{$ruleSet['affiliateId']}}/set/{{$ruleSet['id']}}/n/25";

            $('#publish-confirm .content').load(url, function(){
                $('body').scrollTop(0);
                $("#publish-confirm").modal("toggle");
            });

        };
    });
    </script>

<!-- Modal -->
<div id="publish-confirm" data-toggle="modal" class="modal fade modal-dialog modal-md" data-backdrop="static" role="dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Publish Confirmation</h4>
      </div>
      <div class="modal-body content">
      </div>
    </div>
</div>

    <div class="box" id="rule-set-wrapper">

        <form class="form-inline" role="form">
            <button type="button" class="btn btn-primary" onclick='addNewRule()'>Add New</button>
            @if($hideOffs == 'off')
                <button type="button" class="btn btn-primary" onclick="javascript:refresh(null, 'on')">Show OFF's</button>
            @else
                <button type="button" class="btn btn-primary" onclick="javascript:refresh(null, 'off');">Hide OFF'</button>
            @endif

            <?php
                $arr = array('all' => 'All Products') + App\Models\AffiliateRules::getProductTypeFilterSelect($ruleSet);
                $arr = array_map(function($key) { $arr[$key] = ucfirst ($key); return ucfirst($key); }, $arr);
            ?>


            {!! Form::select('product-type-filter', $arr + App\Models\AffiliateRules::getProductTypeFilterSelect($ruleSet), '', ['class' => 'form-control pull-right', 'id' => 'product-type-filter', 'onchange' => 'refresh(null, null, $("#product-type-filter").val())'])  !!}
            <hr>
            <div class="message error" id="error-container" style="display: none">
            <p style="color:red"></p>
            </div>
        </form>
        <label class='bread-crumbs'>
            <a href="{{ url('/rules/') }}">{{$ruleSet['affiliateName']}}</a>
            >>> <a href="#">{{$ruleSet['ruleSetName']}}</a>
        </label>
        <div class="box" id="rule-set-tbl-wrapper">
        <table id="rule-set-tbl" class="table table-bordered table-striped hover dt-responsive wrap" cellspacing="0" width="100%">
            <thead>
                <tr id="0" class="rule-set-headers nodrag">
                    <th>Rule Name</th>
                    <th>Product type</th>
                    <th>Conditions</th>
                    <th>Actions</th>
                    <th>State</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
    @foreach($ruleSet['rules'] as $rule)
            <?php
                $currentRuleName = $rule['name'];
                $currentProductType = $rule['productType']['productType'];
                $ruleState = $rule['state']['state'];
                $currentRuleId = $rule['id'];
                $currentRuleDomain = $rule['domain'];
                $hideOffCSS = $hideOffs == "off" && $ruleState == "OFF" ? "hide-off": "";
                $hideFilterByTypeCss  = $productType != "all" && $productType != $currentProductType ? "hide-rule-type": "";
                $ruleDomainCss = $rule['domain'] == 'merchant' ? 'active' : '';
            ?>

        @if($ruleId == $currentRuleId)
            <tr class="current-edit-rule {{$ruleDomainCss}} {{$hideOffCSS . ' ' . $hideFilterByTypeCss}}" id="rule-{{$rule['id']}}" data-rule-domain='{{$rule['domain']}}' data-rule-operation='{{$rule['operation']}}' >
                <td class="rule-name maincol">
                    <input type="text" value="{{$currentRuleName}}" maxlength="100" id="rule-name" class="form-control">
                </td>
                <td class="product-type maincol">
                    <div class="product-type-board"></div>
                    <script type="text/javascript">
                    $(document).ready(function(){
                        addProductTypeCriteria('{{$currentRuleId}}', '{{ App\Helpers\JsSanitizer::sanitizeString($currentProductType) }}');
                    });
                    </script>
                </td>
                <td class="conditions maincol">
                    <div class="conditions-board"></div>
                    <div class="conditions-criteria-panel-wrapper"></div>
                    <script type="text/javascript">
                        $(function(){
                            @foreach($rule['conditions'] as $conditionId=>$conditionValue)
                                addConditionCriteria('{{$currentRuleId}}', '{{$conditionId}}', '{{ App\Helpers\JsSanitizer::sanitizeString($conditionValue) }}');
                            @endforeach
                            addConditionsCriteriaPanel('{{$currentRuleId}}');
                                    @if($rule['userFacts'] != "")
                                    var userFacts = '{{$rule['userFacts']}}';
                                    userFacts = userFacts.replace(/&quot;/g, '"');
                                    userFacts = userFacts.replace(/(?:\r\n|\r|\n)/g, '');
                                     var userFacts = JSON.parse(userFacts);
                                    for(var userFactName in userFacts)
                                    {
                                        if(userFactName in userFacts)
                                        {
                                            for(var condition in userFacts[userFactName])
                                            {
                                                addUserFactsCriteria('{{$currentRuleId}}', condition,userFacts[userFactName][condition],userFactName);
                                            }
                                        }
                                    }
                                    @endif
                        });
                    </script>

                </td>
                <td class="actions maincol">
                    <div class="actions-board"></div>
                    <div class="actions-criteria-panel-wrapper"></div>
                    <script type="text/javascript">
                        $(function(){
                            @foreach($rule['actions'] as $actionId=>$actionValue)
                                addActionCriteria('{{$currentRuleId}}', '{{$actionId}}', '{{ App\Helpers\JsSanitizer::sanitizeString($actionValue) }}');
                            @endforeach
                            addActionsCriteriaPanel('{{$currentRuleId}}');
                        });
                    </script>
                </td>
                <td class="state maincol">
                    <div class="state-board"></div>
                    <script type="text/javascript">
                        $(function(){
                            addStateCriteria('{{$currentRuleId}}', '{{ App\Helpers\JsSanitizer::sanitizeString($ruleState) }}');
                        });
                    </script>
                </td>
                <td class="edit-save maincol">
                    <input type="hidden"value="{{$currentRuleId}}" id="rule-id">
                    <input type="hidden" value="{{$rule['priority']}}" id="rule-priority">
                    <div class='edit-save-container'>
                        <a href='javascript:saveRule({{$ruleId}})'>Save</a> |
                        <a href='javascript:refresh()'>Cancel</a> |
                        <a class='delete-rule' href='javascript:deleteRule({{$rule['id']}})'>Delete </a>
                    </div>
                </td>
            </tr>
            @else
                <tr class="odd gradeX {{$hideOffCSS . ' ' . $hideFilterByTypeCss}}" id="rule-{{$rule['id']}}" data-rule-domain='{{$rule['domain']}}' data-rule-operation='{{$rule['operation']}}' >
                    <td class="rule-name"><a href="javascript:editRule('{{$rule['id']}}');">{{$rule['name']}}</a></td>
                    <td class="product-type">
                        @foreach($rule['productType'] as $productId=>$productValue)
                            {{$productValue}} <br />
                        @endforeach
                    </td>
                    <td class="conditions" style="word-break: break-word;">
                        @foreach($rule['conditions'] as $conditionId=>$conditionValue)
                            <?php $conditionValue = (strlen($conditionValue) > 100 ? substr($conditionValue, 0, 99) . '...' : $conditionValue) ?>
                            <div class="condition-item" >{{$conditionId}}: {{$conditionValue}}</div>
                        @endforeach


                        @if(isset($rule['userFacts']))
                            <?php
                                $userFactsData = json_decode($rule['userFacts'], true);
                                $str_array = array();
                                ?>
                            @foreach($userFactsData['personalCounters'] as $userFactName=>$userFact)
                                <?php $str_array[] = $userFactName . ' : ' . $userFact; ?>
                            @endforeach
                            <?php
                            $userFactsString = implode(", ",$str_array);
                            $userFactsString = (strlen($userFactsString) > 100 ? substr($userFactsString, 0, 99) . '...' : $userFactsString);
                            ?>
                            <div class="condition-item" >{{$userFactsString}}</div>
                        @endif
                    </td>
                    <td class="actions">
                        <div class="action-list">
                            @foreach($rule['actions'] as $actionKey=>$actionValue)
                                <?php $defaultRuleActions = $ruleSet['rules'][$ruleSet['defaultRuleId']]['actions'] ?>
                                @if(!array_key_exists($actionKey,$defaultRuleActions) || $defaultRuleActions[$actionKey] != $actionValue)
                                    <span class="action-item label label-warning">{{$actionKey}}: {{$actionValue}}</span>
                                @else
                                    <span class="action-item label label-default">{{$actionKey}}: {{$actionValue}}</span>
                                @endif
                            @endforeach
                        </div>
                    </td>
                    <td class="state">{{$rule['state']['state']}}<br /></td>
                    @can('edit', Resource::get('rules'))
                        <td class="edit">
                            <input type="hidden" value="{{$rule['id']}}" id="rule-id">
                            <input type="hidden" value="{{$rule['priority']}}" id="rule-priority">
                            <a id="edit" href="javascript:editRule('{{$rule['id']}}')">Edit</a>
                        </td>
                    @endcan
                </tr>
        @endif
    @endforeach

    </tbody>
    </table>

    @include('behaviorrules.rule-set-simulator', ['ruleSet' => $ruleSet, 'token' => csrf_token()])

    @can('edit', Resource::get('behavior-rules'))
        <div class="box grid" id="rule-set-publish">
            <button onclick="testAndPublish();" class="btn rule-set-publish btn-danger">Publish Changes</button>
            <div class="loading" style="display:none">

            </div>
        </div>
        <div id="confirm-publish-dialog" title="Test and Publish"></div>
        <br>
    @endcan
<div class="clear"></div>
@endsection

@section('custom-javascript')
<style>
    .hide-rule-type{
        display:none;
    }
    .hide-off{
        display: none;
    }
</style>

    <script src="{{ url('scripts/jquery.tablednd.0.7.min.js') }}" type="text/javascript"></script>

    <script src="{{ url('scripts/jquery.elastic.source.js') }}" type="text/javascript"></script>
    <script src="{{ url('scripts/underscore-min.js') }}" type="text/javascript"></script>

    <script src="{{ url('scripts/datatables.js') }}" type="text/javascript"></script>
    <script src="{{ url('scripts/dataTables.bootstrap.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
    $('#rule-set-tbl').dataTable( {
      "ordering": false,
      "fixedColumns": true,
      "paging": false,
      "columnDefs": [
        {"width": "17%", "targets": [0]},
        {"width": "10%", "targets": [1]},
        {"width": "20%", "targets": [2]},
        {"width": "28%", "targets": [3]},
        {"width": "8%", "targets": [4]},
        {"width": "8%", "targets": [5]}
        ]
    } );
    $('#rule-set-simulator').dataTable( {
        "searching" : false ,
        "ordering": false,
        "fixedColumns": true,
        "paging": false,
        "columnDefs": [ {
        "targets": null,
        "data": null,
        } ]
    } );
    $('#rule-set-list-tbl').dataTable( {
        "columnDefs": [
        {"width": "50%", "targets": [0]},
        {"width": "10%", "targets": [1]},
        {"width": "10%", "targets": [2]},
        {"width": "10%", "targets": [3]},
        {"width": "10%", "targets": [4]},
        {"width": "80%", "targets": [5]}
    ]
    } );

    </script>

<script type="text/javascript">
    window.addEventListener('sessionchanged', function (e) {
        window.location.href = '{{ url('/rules') }}';
    });
</script>


<script type="text/template" id="template-action-show-item">
    <ul class="action-show-item">
        <li class="action-show-rule"><%=rule%> &gt; <%=actionName%>: <%=actionValue%></li>
    </ul>
</script>

<!-- critieria  panels templates start -->
<script type="text/template" id="template-conditions-criteria-panel">
    <table width=100%>
        <tr>
            <td>
                <div class="add-condition">
                    <b>Fact <small>>></small></b>
                </div>
            </td>
            <td>
                {!! Form::select('conditions-criteria-selector', array('-1' => 'Select') + App\Models\AffiliateRules::getConditionsSelector($ruleSet,1), '', ['class' => 'conditions-criteria-selector form-control'])  !!}
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div class="userfacts-board">
                    @foreach($ruleSet['configuration']['userFacts'] as $user_fact_name=>$user_fact)
                        <div class='{{$user_fact_name}}-board board' namespace='{{$user_fact_name}}'></div>
                    @endforeach
                </div>
            </td>
        </tr>
        @foreach($ruleSet['configuration']['userFacts'] as $user_fact_name=>$user_fact)
            <tr>
                <td>
                    <div class="add-condition userfacts-row" style='clear:both;'>
                        <b>
                            <?php
                            $user_fact_name_spaced =ucfirst(preg_replace('/([A-Z])/', ' $1', $user_fact_name));
                            echo $user_fact_name_spaced;
                            ?>
                            <small>>></small>
                        </b>
                    </div>
                </td>
                <td>
                    {!! Form::select('userfacts-criteria-selector', array('-1' => 'Select') + App\Models\AffiliateRules::getUserFactsSelector($user_fact,1), '', ['id' => $user_fact_name , 'class' => 'userfacts-criteria-selector userfacts-row form-control'])  !!}
                </td>
            </tr>
        @endforeach
    </table>

    <script type="text/javascript">
        $(function(){
            $('#rule-<%=ruleId%> .conditions-criteria-panel-wrapper .conditions-criteria-selector').change(function(){
                var _val = $(this).val();
                if(_val == -1)
                    return;

                addConditionCriteria('<%=ruleId%>', _val);
                normalizeConditionByProductType(_val);
            });

            $('#rule-<%=ruleId%> .conditions-criteria-panel-wrapper .userfacts-criteria-selector').change(function(){
                var namespace = $(this).attr('id');
                var _val = $(this).val();
                if(_val == -1)
                    return;
                addUserFactsCriteria('<%=ruleId%>', _val,'',namespace);

            });

            $('#productType').change(function() {
                var product_type = getCurrentProductType();
                $('[class^="show_on_"]').hide();
                $('.show_on_'+product_type).show();
            });
            $('#rule-<%=ruleId%> .conditions-criteria-panel-wrapper .conditions-criteria-selector option').each(function() {

                var product_type = getCurrentProductType();
                if($(this).val() != '-1')
                {
                    var productTypes = getProductTypes();
                    var option_split = $(this).val().split('-');
                    if(option_split.length == 2 && $.inArray(option_split[0],productTypes) > -1)
                    {
                        $(this).addClass('show_on_'+option_split[0]);
                        if(product_type != option_split[0]) $(this).hide();
                    }
                }

            });
        });
    </script>
</script>


<script type="text/template" id="template-actions-criteria-panel">
    <div class="element-id">
        Add Action >>
    </div>
    {!! Form::select('actions-criteria-selector', array('-1' => 'Select') + App\Models\AffiliateRules::getActionsSelector($ruleSet,1), '', ['class' => 'actions-criteria-selector form-control'])  !!}
    <script type="text/javascript">
        $(function(){

            $('#rule-<%=ruleId%> .actions-criteria-panel-wrapper .actions-criteria-selector').on('change', function(){
                if($(this).val() == -1)
                    return;
                addActionCriteria('<%=ruleId%>', $(this).val());
            });
        });
    </script>
</script>

<script type="text/template" id="template-simulator-conditions-criteria-panel">
    <table width=50%>
        <tr>
            <td colspan=2>
                <div class="add-condition" style='clear:both'>
                    <b>Fact<small>>></small></b>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                {!! Form::select('conditions-criteria-selector', array('-1' => 'Select') + App\Models\AffiliateRules::getSelectedAndResponseConditionsSelect($ruleSet), '', ['class' => 'conditions-criteria-selector form-control'])  !!}
            </td>
        </tr>
    </table>
    <script type="text/javascript">
        $(function(){
            $('#rule-set-simulator .conditions-criteria-panel-wrapper .conditions-criteria-selector').change(function(){

            var _val = $(this).val();
            if(_val == -1) return;

            var context = $('#rule-set-simulator');

            //a hack: will eventually translated to rule-set-simulator. which is the id of the simulator table
            addConditionCriteria('set-simulator', _val);
            normalizeConditionByAllProductTypes(_val,context);
            });
        });
    </script>
</script>


<script type="text/javascript">
    var mode = '{{$history}}';
    var oldIndex;
    var oldPriority;

    $(function() {
        //setting default rule
        $('#rule-{{$ruleSet['defaultRuleId']}}').addClass('nodrag nodrop default-rule');
        $('#rule-{{$ruleSet['defaultRuleId']}} #state').prop("disabled", "disabled");
        $('#rule-{{$ruleSet['defaultRuleId']}} #productType').prop("disabled", "disabled");
        $('#rule-{{$ruleSet['defaultRuleId']}} #rule-name').prop("disabled", "disabled");

        $('#product-type-filter option[value="{{$productType}}"]').prop('selected', true);

        console.warn('show-rule-set.blade.php: jquery dialog not supported, move to bootstrap modal');
        /*$("#confirm-publish-dialog").dialog({autoOpen: false,
            width: 'auto',
            height: 'auto',
            modal:true,
            position:{
                at:'center',
                of: window
            }});
        */
        if(window.location.hash == '#dirty')
            bindUnSavedChangesAlert();

        //if in history mode, disable all links, so the table won't be editable
        if(mode == 'history')
        {
            $('.edit-save-container').remove();
            $('#rule-set-publish').remove();
            //$('#rule-set-wrapper a').attr('href', 'javascript: return false;');
            //$('#rule-set-wrapper a').css('cursor', 'default');
        }
        else{
            loadTableDnd(); //else, load the drag & drop table
        }
    });

</script>
@endsection
