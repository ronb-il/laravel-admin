<div class="box" id="simulator-wrapper">
    <h2>Simulator
        <select id="simulator_product_type" class="form-control" style='float:right;width:200px;'>
                <option value='product'>Product</option>
                <option value='cart'>Cart</option>
        </select>
    </h2>
    <div style='clear:both'></div>
    <div class="block" id="rule-set-simulator-wrapper">
        <table id="rule-set-simulator" class="table table-bordered hover dt-responsive wrap">
            <thead>
                <tr>
                    <th>
                        Conditions
                    </th>
                    <th>
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="criterias">
                            <div class="conditions-board"></div>
                            <div class="userfacts-board">
                                <div class='personalCounters-board board' namespace='personalCounters'></div>
                            </div>
                            <div class="conditions-criteria-panel-wrapper">
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
                            </div>
                            <div class="userfacts-criteria-panel-wrapper">
                                <table width=50%>
                                    @foreach($ruleSet['configuration']['userFacts'] as $user_fact_name => $user_fact)
                                        <tr>
                                            <td>
                                                <div class="add-condition" style='clear:both'>
                                                    <b><?php echo ucfirst(preg_replace('/([A-Z])/', ' $1', $user_fact_name)); ?><small>>></small></b>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                {!! Form::select('userfacts-criteria-selector', array('-1' => 'Select') + App\Models\AffiliateRules::getConditionsFromUserFactsSelect($user_fact), '', ['id' => $user_fact_name, 'class' => 'userfacts-criteria-selector form-control'])  !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                    </td>
                    <td class="action-show">
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="box grid">
            <button id="simulateRuleSet" class="btn rule-set-publish btn-primary">Simulate</button>
        </div>
        <br>
    </div>
</div>

<script type="text/javascript">
$(function(){
    function addDefaultSimulationElements() {
        var responseConditions = {!! json_encode($ruleSet['configuration']['responseConditions']) !!}
        for(condition in responseConditions) {
            if (responseConditions[condition]['mandatory'] === "true") {
                addCriteria('set-simulator', condition, responseConditions[condition]['defaultValue'], 'conditions-board', true);
            }
        }
    }

    addDefaultSimulationElements();

    $('#rule-set-simulator .userfacts-criteria-panel-wrapper .userfacts-criteria-selector').change(function(){
        var namespace = $(this).attr('id');
        var _val = $(this).val();
        if(_val == -1)
            return;
        addUserFactsCriteria('set-simulator', _val, '', namespace);
        //a hack: will eventually translated to rule-set-simulator. which is the id of the simulator table
    });

    $('#rule-set-simulator .conditions-criteria-panel-wrapper .conditions-criteria-selector').change(function(){

        var _val = $(this).val();
        if(_val == -1) return;

        var context = $('#rule-set-simulator');

        //a hack: will eventually translated to rule-set-simulator. which is the id of the simulator table
        addConditionCriteria('set-simulator', _val);
        normalizeConditionByAllProductTypes(_val,context);
    });

    $("#simulateRuleSet").on("click", function(){
        _data = {};
        _data.productType = $('#simulator_product_type').val();
        _data.affiliateId= '{{ $ruleSet['affiliateId'] }}';
        _data.ruleSetId = '{{ $ruleSet['id'] }}';
        _data.conditions = {};
        _data.userFacts = {};

        $('.userfacts-board .board').each(function() {
            var userfacts_obj = $(this);
            var namespace = userfacts_obj.attr('namespace');
            _data.userFacts[namespace] = {};

            $.each(userfacts_obj.find('input, select, textarea'), function(index, element){
                _data.userFacts[namespace][element.id] = element.value;
            });
        });

        $.each($('#rule-set-simulator .conditions-board').find('input, select, textarea'), function(index, element){
            _data.conditions[element.id] = element.value;
        });

        $.ajax({
            url: "/rules/rule-set/rule/simulate-rule-set",
            type: "POST",
            dataType: "json",
            data: {data: JSON.stringify(_data), _token : '{{ $token }}' },
            success: function(data){
                if(data.status != "true"){
                    displayError(data.message);
                    return;
                }

                //DISPLAY ACTIONS
                var actionShow=data.message.actionShow;
                var actionShowBox = $('#rule-set-simulator .action-show').empty();
                var actionItemTemplate = $('#template-action-show-item');


                var foreachAction = function(i, action){
                    $(actionShowBox).append(_.template(actionItemTemplate.html(), {rule: action.ruleName, actionName: i,actionValue:action.value}));
                };

                $.each(actionShow, foreachAction);

                actionShowBox.fadeIn();
                actionShowBox.fadeOut();
                actionShowBox.fadeIn();
            },
            error: function(){
                displayError("Fail to simulate: is web server up?");
            }
        });
    });
});
</script>
