Netotiate.Retailer.Rules = (function(){
	var privateMethods = {};
	
	privateMethods.forEachEditableCell = function(table, ruleId, func){
		$("." + table + ".active-rules").find("tr[data-rule-id='"+ ruleId + "']").find("td[data-edtiable='true']").each(func);
	};
	
	privateMethods.forEachValueInCsv = function(csv, func){
		if(typeof(csv) != 'string')
			return;
		var parts = csv.split(/\s*\,\s*/);
		$.each(parts, func);
	}

	privateMethods.forEachRule = function(table, func){
		$("." + table + ".active-rules").find("tr.rule-container").each(func);
	};
	
	privateMethods.makeEditable = function(index, element){
		var $element = $(element);
		var value = $element.attr("data-value");
		var $input = $("<textarea>").attr('maxLength', '3000').val(value);
		$element.find(".value").replaceWith($input);
	};
	
	privateMethods.unMakeEditable = function(index, element){
		var $element = $(element);
		var value = $element.attr("data-value");
		var $value = $("<span class='value'>").text(value);
		$element.find("textarea").replaceWith($value);
	};
	
	privateMethods.updateValues = function(index, element){
		var $element = $(element);
		var value = $element.find("textarea").val();
		$element.attr("data-value", value)
	};
	
	privateMethods.validateCell = function(index, element){
		var $element = $(element);
		var value = $element.find("textarea").val();
		var name = $element.attr('data-name');
		return privateMethods.validateUiConfig(name, value);
	};
	

	privateMethods.editMode = function(table, ruleId){
		var $rule = $("." + table + ".active-rules").find("tr[data-rule-id='"+ ruleId + "']").addClass('edit-mode');
		$rule.removeClass('view-mode');
		var $inputs = $rule.find('textarea');
		if($inputs.length > 0)
			$inputs[0].focus();
		
	};
	
	privateMethods.newRuleMode = function(table, ruleId){
		$("." + table + ".active-rules").find("tr[data-rule-id='"+ ruleId + "']").addClass('new-rule-mode');
	};
	
	privateMethods.viewMode = function(table, ruleId){
		var $rule = $("." + table + ".active-rules").find("tr[data-rule-id='"+ ruleId + "']").addClass('view-mode');
		$rule.removeClass('edit-mode');
		$rule.removeClass('new-rule-mode');
	};
	
	privateMethods.handleApiResponse = function(success, fail){
		return function(data){
			if(data.status == 'true'){
				success(data);
			}
			else{
				if(typeof fail == 'function')
					fail(data);
			}
		};
	};
	
	privateMethods.disableChangingRules = function(){
		$("input[name='service-status']").prop("disabled", true);
		$("#publish").prop("disabled", true);
		$("#publish").addClass("disabled");;
	};
	
	privateMethods.enableChangingRules = function(){
		$("input[name='service-status']").prop("disabled", false);
		$("#publish").removeClass("disabled", false);
		$("#publish").prop("disabled", false);
		$("#publish").removeClass("disabled");
	};

	privateMethods.validateUiConfig = function(attrName, value){
		if(!Netotiate.Retailer.Rules.uiConfig[attrName])
			return true;
		
		var matches = new RegExp(Netotiate.Retailer.Rules.uiConfig[attrName].matches);
		if(!matches.test(value)){
			alert(_t(Netotiate.Retailer.Rules.uiConfig[attrName].ifNotMatches));
			return false;
		}
		
		return true;
	};
	
	var myRules = {};
	
	myRules.editRule = function(table, ruleId){
		privateMethods.forEachEditableCell(table, ruleId, privateMethods.makeEditable);
		privateMethods.editMode(table, ruleId);
	}
	
	myRules.cancelEdit = function(table, ruleId){
		privateMethods.forEachEditableCell(table, ruleId, privateMethods.unMakeEditable);
		privateMethods.viewMode(table, ruleId);
	}
	
	myRules.saveRule = function(table, ruleId){
		var valid = true;
		
		privateMethods.forEachEditableCell(table, ruleId, function(index,element){
			if(!privateMethods.validateCell(index,element)){
				var $element = $(element);
				$element.find('textarea').focus();
				valid = false;
				return false;
			}
		});
		
		if(!valid){
			return;
		}

		//validate no duplicates
		$condition1 = $("." + table + ".active-rules").find("tr[data-rule-id='"+ ruleId + "']").find("td[data-type='condition'][data-name='category']").find('textarea').val();
		privateMethods.forEachValueInCsv($condition1, function(index, subset1){
			privateMethods.forEachRule(table, function(index,rule){
				var $rule = $(rule);
				var rule2Id = $rule.attr("data-rule-id");
				if(rule2Id == ruleId) //continue if we're on the same rule
					return;

				$condition2 = $("." + table + ".active-rules").find("tr[data-rule-id='"+ rule2Id + "']").find("td[data-type='condition'][data-name='category']").attr('data-value');
				privateMethods.forEachValueInCsv($condition2, function(index,subset2){
					if(subset1 == subset2){
						alert(_t("MerchantDashboard.category_already_exists"));
						valid = false;
					}
				});
			});
		});
		//validate no duplicates
		
		if(!valid){
			return;
		}

		privateMethods.forEachEditableCell(table, ruleId, function(index,element){			
			privateMethods.updateValues(index,element);
			privateMethods.unMakeEditable(index,element);
		});
		
		
		privateMethods.viewMode(table, ruleId);
	}
	
	myRules.addNewInclusionRule= function(){
		var $conditions = $(".inclusions.active-rules").find(".add-new-rule-container").find(".conditions");
		var $actions = $(".inclusions.active-rules").find(".add-new-rule-container").find(".actions");
		var query = {rcondition: $conditions.val(), raction: $actions.val()};
		
		function success(data){
			$(".inclusions.active-rules").find("tr.add-new-rule-container").before(data.message);
			myRules.editRule('inclusions', data.ruleId);
			privateMethods.newRuleMode('inclusions',data.ruleId);
		};
		
		function fail(data){
			alert('fail to add new rule.');
		};
		
		$.ajax({url: '/info/new-inclusion-rule',
				data: query,
				dataType: 'json',
				success: privateMethods.handleApiResponse(success, fail),
				fail: fail});
	}
	
	
	myRules.addNewExclusionRule = function(){
		var $conditions = $(".exclusions.active-rules").find(".add-new-rule-container").find(".conditions");
		var query = {rcondition: $conditions.val()};
		
		function success(data){
			$(".exclusions.active-rules").find("tr.add-new-rule-container").before(data.message);
			myRules.editRule('exclusions', data.ruleId);
			privateMethods.newRuleMode('exclusions',data.ruleId);
		};
		
		function fail(data){
			alert('fail to add new rule.');
		};
		
		$.ajax({
				url: '/info/new-exclusion-rule',
				data: query,
				dataType: 'json',
				success: privateMethods.handleApiResponse(success, fail),
				fail: fail
				});
	};
	
	myRules.addNewRuleView = function(table){
		$("." + table + ".active-rules").removeClass('view-mode');
	}
	
	myRules.cancelAddNewRuleView = function(table){
		$("." + table + ".active-rules").addClass('view-mode');
	}
	
	myRules.deleteRule= function(table, ruleId){
		if(!confirm(_t('MerchantDashboard.are_you_sure_you_want_to_delete_this_rule')))
			return false;
		$("." + table + ".active-rules").find("tr[data-rule-id='"+ ruleId + "']").remove();
	}
	
	myRules.updateServiceStatus = function(state){
		var val = state ? 'true' : 'false'
		var stateClass  = state ? 'enabled' : 'disabled';

		function success(data){
			alert(_t(data.message));
			privateMethods.enableChangingRules();
			
			$('.bra-header').removeClass('enabled').removeClass('disabled').addClass(stateClass);
		}
		
		function fail(data){
			alert(_t(data.message));
			privateMethods.enableChangingRules();
		}
		
		var query = {status: val}
		
		function publish(){
			$.ajax({
				url: '/info/update-service-status',
				data: query,
				dataType: 'json',
				success: privateMethods.handleApiResponse(success, fail),
				fail: fail	
			});
		}
		
		var message = val == 'true' ? _t('MerchantDashboard.are_you_sure_you_want_to_activate_netotiate') : _t('MerchantDashboard.are_you_sure_you_want_to_deactivate_netotiate');
		if(!confirm(message)){
			event.preventDefault();
			return;
		}
		
		privateMethods.disableChangingRules();
		publish();
	}
	
	myRules.publish = function(){
		
		function collectRules(){
			var data = [];
			
			privateMethods.forEachRule('inclusions', function(index, element){
				var $element = $(element),
				$condition = $element.find("*[data-type='condition']"),
				$action = $element.find("*[data-type='action']");
				var rule = {operation: 'include', conditions :{}, actions: {}};
				rule.conditions[$condition.attr('data-name')] = $condition.attr('data-value');
				rule.actions[$action.attr('data-name')] = $action.attr('data-value');
				data.push(rule);
			});
			
			privateMethods.forEachRule('exclusions', function(index, element){
				var $element = $(element),
				$condition = $element.find("*[data-type='condition']");				
				var rule = {operation: 'exclude', conditions :{}};
				rule.conditions[$condition.attr('data-name')] = $condition.attr('data-value');
				data.push(rule);
			});
			
			return data;
		};
		
		function validateNoRulesInEditMode(){
			var valid = true;

			function isInEditMode(index, element){
				var $element = $(element);
				if($element.hasClass("edit-mode")){
					valid = false;
					return false;
				}
			}

			privateMethods.forEachRule("inclusions", isInEditMode);
			privateMethods.forEachRule("exclusions", isInEditMode);

			return valid;
		}

		function success(data){
			alert(_t(data.message));
			privateMethods.enableChangingRules();
		}
		
		function fail(data){
			alert(_t(data.message));
			privateMethods.enableChangingRules();
		}
		
		function publish(data){
			$.ajax({
				url: '/info/rules-publish',
				data: {rules: data},
				type: "post",
				dataType: "json",
				success: privateMethods.handleApiResponse(success, fail),
				fail: fail
			});
		};
		
		if(!validateNoRulesInEditMode()){
			alert(_t('MerchantDashboard.please_save_all_rules_before_publish'));
			return;
		}

		if(!confirm(_t('MerchantDashboard.are_you_sure_you_want_to_publish')))
			return;

		privateMethods.disableChangingRules();
		var data  = collectRules();
		publish(data)
	}
	
	return myRules;
}());
