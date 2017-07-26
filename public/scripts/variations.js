var Personali = Personali || {};
Personali.lab = (function(){
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var _lab = this;
    _lab.chosen_affiliate_id = -1;
    _lab.hideErrorsAfter = 4000;
    _lab.variationsConfig = {};
    _lab.dataSources= {};

    /* utilities */

    var createRndId = function(_prefix) {
        var prefix = _prefix || "var_";
        return prefix + parseInt(new Date().getTime() / 1000, 10)  + Math.floor(Math.random() * 0x75bcd15) + 1;
    }

    var lowerNoSpace = function(str) {

        if(str.indexOf(' ') > -1)
        {
            return lowerNoSpace(str.replace(' ','_'));
        }
        else
        {
            return str.toLowerCase();
        }
    }

    var getTemplate = function(template_id) {
        return $('.variations-templates #'+template_id).clone().attr('id','');
    };

    var notify = function(type,msg) {

        $('#variations-notifier').removeClass()
            .addClass(type)
            .html(msg)
            .fadeIn("fast",function() {
                setTimeout(hide_errors, _lab.hideErrorsAfter)

            });
    }
    var hide_errors = function() {
        $('#variations-notifier').fadeOut('slow');
    }
    var isConflicted = function(name,row_object) {
        // already an array
        var conflicts_with = _lab.getConfigFieldByName(name, "conflicts");
        if(conflicts_with)
        {
            var flag = false;
            row_object.find('.variations-content-form').each(function() {
                var the_form = $(this);
                var action_name = $(this).attr('action_name');
                if($.inArray(action_name, conflicts_with) > -1)
                {
                    // console.log(action_name)
                    the_form.parents('.variation-content-box').addClass('conflict');
                    flag = true;
                }
            });
            return flag;
        }
        return false;

    };
    var parseActionJson = function(row_object) {

        var action_json = {};
        var flag = true;
        row_object.find('.variation-content-box').removeClass('validation_err').removeClass('conflict').removeClass('selected');
        row_object.find('.variations-content-form').each(function() {

            var the_form = $(this);
            var parent_box = the_form.parents('.variation-content-box').eq(0);


            var name = parent_box.find('.action-name').text().trim();
            if(name == "-1" || name == "")
            {
                flag = false;
                parent_box.addClass('validation_err');
                notify("error","Must select variation type");
                return false;
            }
            else if(name in action_json)
            {
                flag = false;
                parent_box.addClass('validation_err');
                notify("error","Variation can appear only once");
                return false;
            }
            else if(isConflicted(name,row_object))
            {
                parent_box.addClass('conflict');
                notify("error",name+" conflicts with other actions");
                flag = false;
                return false;
            }
            else
            {
                if(validateBoxForm(the_form))
                {
                    action_json[name] = the_form.serializeArray();
                }
                else
                {
                    flag = false;
                    notify("error","Values are not valid");
                    return false;
                }
            }

        });

        if(flag)
        {
            // set weight
            Object.keys(action_json).forEach(function (key) {
                var keys = [];
                var divider = 0;
                //
                $.each(action_json[key], function(k,v) { keys.push(v.name) });
                // distinct with count
                var count = keys.reduce(function(countMap, word) {countMap[word] = ++countMap[word] || 1; return countMap}, {});
                // set how much to divde by
                optionsLentgh = action_json[key].length;;

                for (var i = 0; i < optionsLentgh; i++) {
                    divider = count[action_json[key][i].name];
                    var avWeight = 100 / divider;
                    avWeight = (avWeight % 1 != 0) ? parseFloat(avWeight).toFixed(1) : parseInt(avWeight);
                    action_json[key][i].weight = avWeight;
                }
            });

            var jsonString = JSON.stringify(action_json, null, 4);
            // console.log(jsonString);
            row_object.find('.variation-json').val(jsonString);
        }
        return flag;
    }

    var parseRow = function(row_object) {

        var json = {'validated':true,'data':{},'msg':''};
        var name = $.trim(row_object.find('.variation-name').val());

        if(name == "" || !validateUniqueValue('.variation-name', name))
        {
            json.validated = false;
            json.msg = "Variation name should be non empty & unique";
            row_object.find('.variation-name').addClass('validation_err');
            return json;
        } else {
            json.data.name = $.trim(row_object.find('.variation-name').val());
        }

        if(parseActionJson(row_object)) {
            json.data.json = row_object.find('.variation-json').val();
            json.data.status = row_object.find('.variation-status').val();
            json.data.description = $.trim(row_object.find('.variation-desc').val());
            json.data.id = row_object.find('.variation-id').val();
            json.data.affiliate_id = _lab.chosen_affiliate_id;
            return json;
        } else {
            return  {'validated':false}
        }
    }

    var validateUniqueValue = function(selector,value) {

        var found = 0;
        $(selector).each(function() {
            if($(this).val().trim().toLowerCase() == value.trim().toLowerCase()) found++;
        });
        return (found == 1);

    }

    var normalizeVariationName = function(name) {
        name = name.replace(' ','_');
        while(name.indexOf(' ') > - 1)
        {
            return normalizeVariationName(name);
        }
        return name.toLowerCase();
    }

    var addBlocker = function(selector_class) {
        var div = $("<div class='blocker'></div").css('height',$('.'+selector_class).css('height'));
        $('.'+selector_class).append(div);
    }
    var removeBlocker = function() {
        $("div.blocker").remove();
    }

    var validateBoxForm = function(the_form) {
        if(the_form.find('.variants input').length == 0) return false;
        return true;
    };
    var fixSetInnerHeight = function(parent_row) {

        parent_row.find('.variation-content-box').each(function() {
            var height_fix = $(this)
                    .find('.graph-preview .variations-content-form')
                    .css('height')
                    .replace('px','') * 1;
            $(this).css('height',height_fix + 15 + 'px');

        })
    }
    /* * * *  */
    _lab.init = function(){

        $(document).ready(function() {

            _lab.populateActions();

            /*
            $('#list-items-table').on('click', '.cancel', function() {

            $('.variants-list li').live('click',function() {
                $('.variants-list li').removeClass('selected');
                $(this).addClass('selected');
            });
            $('.configuration-set-head').live('click',function(e) {
                if(e.target !== this) return;
                $(this).find('span.variations-set-toggler a').trigger('click');
            });

            $(".select2").select2();
            */
        });

    };

    _lab.setConfig = function(json){
        _lab.variationsConfig = json;
    };
    _lab.getConfig  = function(){
        return _lab.variationsConfig;
    };

    _lab.getDataSource = function(key) {

        if(key in _lab.dataSources)
        {
            return _lab.dataSources[key];
        }
        return {};
    }
    _lab.setDataSource = function(json) {
        _lab.dataSources = json;
    }
    _lab.getConfigJsonByName = function(name){

        var json =  {};
        $.each(_lab.getConfig(),function(index,item) {
            if(item.name == name) json = JSON.parse(item.json);
        });
        return json;
    };
    _lab.getConfigFieldByName  = function(name,field){

        var ret =  "";
        $.each(_lab.getConfig(),function(index,item) {
            if(item.name == name) ret = item[field] || "";
        });
        return ret;
    };

    _lab.populateActions = function() {

        var parent_selector = $('#variation-set-template .actions-options');
        $.each(_lab.getConfig(),function(index,item) {
            if(item.status == 1)
            {
                var option = $("<option val='"+item.name+"'>"+item.name+"</option>");
                parent_selector.append(option);
            }
        });
    }

    _lab.addAction = function(obj) {
        var name = $(obj).val();
        if(name == '-1'){
            return false;
        }

        var parent_box = $(obj).parents('.variations-set').find('.variations-set-content').eq(0);
        var new_action = _lab.getPreviewHtml(name);
        parent_box.append(new_action);

        if($(obj).parents('.variations-set').find('a.btn.variation-button').hasClass('close'))
        {
            $(obj).parents('.variations-set').find('a.btn.variation-button').trigger('click');
        }
        fixSetInnerHeight(parent_box);

    };

    _lab.getPreviewHtml = function(name,json_data) {

        var new_action = getTemplate('variation-content-template');
        var rnd_form_id = createRndId('form_');
        new_action.find('.variations-content-form').attr('id',rnd_form_id).attr('action_name',name);
        new_action.find('.variation-content-box-toolbar a').attr('formid',rnd_form_id);
        new_action.find('.preview-variant-name').attr('formid',rnd_form_id);

        if(name) //populate
        {
            json_data = json_data || {};
            var json = _lab.getConfigJsonByName(name);
            var type = _lab.getConfigFieldByName(name,"type");
            var Generator = _lab.formGenerator(json,json_data,type);
            var form_html = Generator.getStructure();
            new_action.find('.variations-content-form').html(form_html);
            new_action.find('.variation-preview-row-content').addClass(normalizeVariationName(name));
            new_action.find('.preview-variant-name').attr('formid',rnd_form_id);
        }
        new_action.find('.action-name').text(name);
        return new_action;

    }
    _lab.addVariation = function(name,json_data) {

        var new_row = getTemplate('variation-content-template');
        $.each(_lab.getConfig(),function(index,item) {

            var option = $("<option value='"+item.name+"'>"+item.name+"</option>");
            if(name)
            {
                if(name == item.name) option.attr('selected','selected');
            }
            new_row.find('.variation-selector').append(option);
        });
        if(json_data && name) //populate
        {

            var json = _lab.getConfigJsonByName(name);
            var type = _lab.getConfigFieldByName(name,"type");
            var Generator = _lab.formGenerator(json,json_data,type);
            var form_html = Generator.getHtml();
            new_row.find('.variations-content-form').html(form_html);
        }
        $('.variations-content-form-container').append(new_row);
    };

    _lab.removeVariationBox = function(obj) {

        var parent_row = $(obj).parents('.variation-content-box').eq(0);
        parent_row.remove();

    }

    _lab.showInfo = function(obj) {
        var customModal = $(' \
            <div class="custom-modal modal fade" role="dialog" aria-hidden="true"> \
                <div class="modal-dialog" style="width:92%"> \
                    <div class="modal-content"> \
                        <div class="modal-header"> \
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> \
                            <div id="devices" class="selectable-image-selector" style="float:right;padding-right:10px;padding-top:2px"> \
                                <ul class="device-types clearfix"> \
                                    <li> \
                                    <input id="desktop" type="checkbox" name="device-type" value="desktop" disabled="disabled" /> \
                                    <label class="selectable-image device desktop" for="desktop"></label> \
                                    </li> \
                                    <li> \
                                    <input id="mobile" type="checkbox" name="device-type" value="mobile" disabled="disabled" /> \
                                    <label class="selectable-image device mobile"for="mobile"></label> \
                                    </li> \
                                    <li> \
                                    <input id="tablet" type="checkbox" name="device-type" value="tablet" disabled="disabled" /> \
                                    <label class="selectable-image device tablet"for="tablet"></label> \
                                    </li> \
                                </ul> \
                            </div> \
                            <h4 class="modal-title">What does it do?</h4> \
                        </div> \
                        <div class="modal-body"></div> \
                        <div class="modal-footer"><button class="btn" data-dismiss="modal">Close</button></div> \
                    </div> \
                </div> \
            </div> \
        ');

        $('body').append(customModal);

        var parent_row = $(obj).parents('.variation-content-box').eq(0);
        var name = parent_row.find(".action-name").text().trim();

        if (name != "-1" && name != "") {
            var content = getConfigFieldByName(name,"description");
            var img_url = getConfigFieldByName(name,"image_url");
            content = $("<div></div>").append(content);
            if (img_url != "") {
                content.append("<br/><img src='"+img_url+"'width=90% align=center height=auto /><br/>");
            }

            var jsonConfig = getConfigJsonByName(name);
            for(device in jsonConfig.devices) {
                // set it to checked
                $("#devices input[name='device-type'][value='" + jsonConfig.devices[device] + "']").attr('checked','checked');
            }

        }

        $('.custom-modal .modal-dialog .modal-content .modal-body').append(content);

        // console.log($('#custom-modal'));
        $('.custom-modal').on('hide.bs.modal', function(){
            $('.custom-modal').remove();
        });

        $('.custom-modal').modal('show');
    }

    _lab.editOptions = function(obj) {
        var form_id = $(obj).attr('formid');
        var parent_row = $(obj).parents('.variation-content-box').eq(0);
        var name = parent_row.find(".action-name").text().trim();
        var json = _lab.getConfigJsonByName(name);
        var Generator = _lab.formGenerator(json);
        var variants_container = $('#variants-modal'); // getTemplate('variants-modal');

        variants_container.find('.variant-name').text(" " + name);
        variants_container.find('.variants-list a').remove(); //clean
        variants_container.find('.variants-list-options .options-selector').html(''); //clean
        variants_container.find('input.editable-form-id').val(form_id);

        $.each(json.fields,function(index,field) {
            var unique_id = createRndId("variant_");
            var li = $("<a class='list-group-item' href=\"#\" onclick=\"Personali.lab.selectVariant('"+unique_id+"',this)\">"+field.name+"</a>");
            variants_container.find('.variants-list').append(li);
            var cls_name = lowerNoSpace(field.name);
            var field_data = [];
            $("#" + form_id + "  .variants." + cls_name + " input").each(function() {
                field_data.push($(this).val());
            });

            var _field = Generator.generateFields(field, field_data);
            var inputs_div = $("<div></div>").html(_field).attr('id',unique_id).addClass('variant-editor');
            variants_container.find('.variants-list ul').append(li);
            variants_container.find('.variants-list-options .options-selector').append(inputs_div);
            variants_container.find('.add-variation-wrapper').hide();
            variants_container.find('.variants-list-options .options-selector .variant-editor').hide()
        });

        variants_container.find('.variants-list-options .options-selector').find(".select2").select2();
        variants_container.modal('show');
    }

    _lab.selectVariant = function(id,obj) {
        $('.editor-form .variant-name-title').html('for <b>'+$(obj).text()+'</b>');
        $('.variant-editor').hide();
        $('#'+id).slideDown();
        $('.add-variation-wrapper').show();
    }

    _lab.saveEditor = function() {
        var flag = true;
        var the_form = $('.editor-form:visible').eq(0);

        the_form.find('select').removeClass('validation_err');

        the_form.find('select.mandatory').each(function() {
            if ($(this).val() == "" || $(this).val() == "-1") {
                $(this).addClass('validation_err');
                notify('error',$(this).attr('name') + ' is mandatory');
                flag = false;
                return flag;
            }
        });

        if (flag) {
            var form_id = $('#variants-modal').find('.editable-form-id').val();

            var editedForm = $('#' + form_id);
            editedForm.find('input').remove();

            $.each(the_form.serializeArray(),function(index, obj) {
                var input = $("<input type='hidden' name='"+obj.name+"' value='"+obj.value+"' />");
                editedForm.find('.variants.'+lowerNoSpace(obj.name)).append(input)
            })

            _lab.generatePreview(form_id);
        }


    }
    _lab.generatePreview = function(form_id) {

        $('#'+form_id+' .variants').each(function() {
            var container = $(this);
            var cls_name = container.attr('variant_cls');
            container.find('.graph-item').remove();
            var items_counter = 0;
            container.find('input').each(function() {
                var val = $(this).val();
                var item = getTemplate('graph-item-template').addClass(cls_name);
                item.find('.item-value').text(val);
                items_counter++;
                container.append(item)
            });
            var width = parseFloat(100 / items_counter);
            container.find('.graph-item').css('width',width+'%');
        })

    }

    _lab.populateForm = function(obj) {

        var parent_row = $(obj).parents('.variation-content-box').eq(0);
        var json = _lab.getConfigJsonByName(obj.value);
        var type = _lab.getConfigFieldByName(obj.value,"type");
        var Generator = _lab.formGenerator(json,undefined,type);
        var form_html = Generator.getHtml();
        parent_row.find('.variations-content-form').html(form_html);
    }

    _lab.cloneVariation = function() {
        parent_row = $('.variant-editor').find('.variation-form-input-box:visible').eq(0);
        var cloned = parent_row.clone();

        cloned.find('.input-holder div').remove();
        cloned.find('.select2').removeClass('select2-offscreen').removeAttr();
        cloned.find('.select2').select2();
        parent_row.parents('div').eq(0).append(cloned);
    }

    _lab.deleteVariation = function(obj) {
        parent_row = $(obj).parents('.variation-form-input-box').eq(0);
        parent_row.remove();
    }

    // $(window).bind('beforeunload', function(){
    // return 'Are you sure you want to leave?';
    // });

    _lab.addSet = function(data){
        if(_lab.chosen_affiliate_id > -1) {
            var new_row = getTemplate('variation-set-template');
            new_row.attr('id',createRndId());
            if(data) //populate
            {
                new_row.find('.variations-set-content').html('');
                new_row.find('.variation-id').val(data.id);
                new_row.find('.variation-status').val(data.status);
                new_row.find('.variation-name').val(data.name);
                new_row.find('.variation-desc').val(data.description);
                new_row.find('.variation-json').val(data.json);

                $.each(JSON.parse(data.json),function(name,json_data) {
                    new_row.find('.variations-set-content').append( _lab.getPreviewHtml(name,json_data));
                });

            }
            $('.variations-sets-container').append(new_row);
            new_row.find('form').each(function() {
                var form_id = $(this).attr('id');
                Personali.lab.generatePreview(form_id);
            });
            new_row.find('.actions-options').addClass('select2').select2();
        }
        else
        {
            notify('error','Please select affiliate');
        }

    };

    _lab.removeSet = function(obj) {

        parent_row = $(obj).parents('.variations-set').eq(0);
        var id = parent_row.find('.variation-id').val();

        var msg = 'Are you sure you want to delete this configuration set ?';
            if (confirm(msg)) {
                var remove_div = function() {
                parent_row.slideDown('slow',function() {
                $(this).remove();
                });
            };
            debugger;
                if(id > -1) {
                    $.ajax({
                        url: "/variations/delete-variation" ,
                        dataType: "json",
                        type: "POST",
                        data: { variation_id : id },
                        success: function(data){
                            var response = data._response || {};
                            if (response.status != "error") {
                                notify("success", response.message);
                                remove_div();
                            } else {
                                notify("error", response.message);
                            }
                        },
                        error: function(){
                            notify("error","Connection Error");
                        }
                    });
                } else {
                    remove_div();
                }
            }
    };

    _lab.changeStatus = function(obj){

        parent_row = $(obj).parents('.variations-set').eq(0);
        if(parent_row.find('.variation-status').val() == "enabled") {
            parent_row.find('.variation-status').val("disabled");
            parent_row.find('.variations-set-tool.status').text('enable');
        }
        else
        {
            parent_row.find('.variation-status').val("enabled");
            parent_row.find('.variations-set-tool.status').text('disable');
        }
        _lab.save(obj);

    }
    _lab.toggle= function(obj){

        parent_row = $(obj).parents('.variations-set').eq(0);
        if($(obj).hasClass('open'))
        {
            parent_row.find('.variations-set-content').slideUp('fast',function() {
                $(obj).text('+').removeClass('open').addClass('close');
            })
        }
        else
        {
            parent_row.find('.variations-set-content').slideDown('fast',function() {
                $(obj).text('-').removeClass('close').addClass('open');
                fixSetInnerHeight($(this));
            })

        }
    };

    _lab.save = function(obj, publish){
        parent_row = $(obj).parents('.variations-set').eq(0);
        parent_row.removeClass('err');

        var json = parseRow(parent_row);

        if(json.validated) {
            $('.validation_err').removeClass('validation_err');
            $.ajax({
                url: "/variations/save-variation" ,
                dataType: "json",
                type: "POST",
                data: { data : JSON.stringify(json.data) },
                success: function(data){
                    var response = data._response || {};
                    if(response.status != "error")
                    {
                        //parent_row.find('.variations-set-tool.status').css('visibility','visible');
                        if('new_id' in response) {
                            parent_row.find('.variation-id').val(response.new_id);
                        }

                        if (publish) {
                            _lab.publish(obj);
                        } else {
                            notify("success", response.message);
                        }
                    }
                    else
                    {
                        notify("error", response.message);
                    }

                },
                error: function(){
                    notify("error","Connection Error");
                }
            });

        }
        else
        {
            notify('error',json.msg);
        }
    };

    _lab.publish = function(obj) {
        parent_row = $(obj).parents('.variations-set').eq(0);
        parent_row.removeClass('err');
        var json = parseRow(parent_row);
        if(json.validated)
        {
            $('.validation_err').removeClass('validation_err');
            $.ajax({
                url: "/variations/publish-variation" ,
                dataType: "json",
                type: "POST",
                data: {data : JSON.stringify(json.data), _token : $("#_token").val() },
                success: function(data){
                    var response = data._response || {};
                    if(response.status != "error")
                    {
                        notify("success", response.message);
                    }
                    else
                    {
                        notify("error", response.message);
                    }

                },
                error: function(){
                    notify("error", "Connection Error");
                }
            });
        }
        else
        {
            notify('error', json.msg);
        }
    };

    _lab.preview = function(obj) {
        parent_row = $(obj).parents('.variations-set').eq(0);
        var json = parent_row.find('.variation-json').val();
        var content = $("<pre></pre>").text(json).css({"min-height":"200px","background-color":"#e1e1e1"});

        var customModal = $(' \
            <div class="custom-modal modal fade" role="dialog" aria-hidden="true"> \
                <div class="modal-dialog" style="width:50%"> \
                    <div class="modal-content"> \
                        <div class="modal-header"> \
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> \
                            <h4 class="modal-title">Preview</h4> \
                        </div> \
                        <div class="modal-body"></div> \
                        <div class="modal-footer"><button class="btn" data-dismiss="modal">Close</button></div> \
                    </div> \
                </div> \
            </div> \
        ');

        $('body').append(customModal);

        $('.custom-modal .modal-dialog .modal-content .modal-body').append(content);

        // console.log($('#custom-modal'));
        $('.custom-modal').on('hide.bs.modal', function(){
            $('.custom-modal').remove();
        });

        $('.custom-modal').modal('show');
    }


    _lab.selectAffiliate = function(id){
        if (id != -1) {
            // _lab.chosen_affiliate_id = id;
            $('h3 .head-affiliate-name').text(' - option.text()');
            _lab.fetchByAffiliate(id);
        } else {
            $('h3 .head-affiliate-name').text('');
        }
        _lab.chosen_affiliate_id = id;
    };


    _lab.selectSet = function(obj){
        $('.variations-set').removeClass('active');
        $(obj).addClass('active');
    };

    _lab.fetchByAffiliate = function(id){

        $('.variations-set:visible').remove();
        if(id > -1)
        {
            $.ajax({
                url: "/variations/fetch-variations" ,
                dataType: "json",
                type: "post",
                data: {affiliate_id: id},
                success: function(data){
                    data = data.data;
                    _lab.setDataSource(data.dataSources);
                    var sets = data.sets;
                    for(var i=0;i<sets.length;i++)
                    {
                        _lab.addSet(sets[i]);
                    }
                },
                error: function(){
                    notify("error","Connection Error");
                }
            });
        }
        else
        {
            notify('error','Please select affiliate');
        }

    };
    _lab.home = function(){

        $('.variations-set-editor').hide();
        $( ".variations-sets-container").animate({
                'margin-left':'0px'
            }
            , '1500'
        );
    };

    _lab.formGenerator = function(json,json_data,type){

        var _form = this;
        _form.html = "";
        _form.json = json;


        _form.getStructure = function() {

            _form.html = $("<div></div>");
            $.each(_form.json.fields,function(index,field) {

                var field_data = [];
                if(json_data)
                {
                    for(var i=0;i<json_data.length;i++)
                    {
                        if(field.name == json_data[i].name) field_data.push(json_data[i].value);
                    }
                }
                _form.html.append(_form.generatePreviewRows(field,field_data));
            });
            return _form.html;
        };


        _form.getHtml = function() {
            _form.html = $("<div></div>");
            $.each(_form.json.fields, function(index, field) {

                var field_data = [];
                if(json_data)
                {
                    for(var i=0;i<json_data.length;i++)
                    {
                        if(field.name == json_data[i].name) {
                            field_data.push(json_data[i].value);
                        }
                    }
                }
                _form.html.append(_form.generateFields(field,field_data));
            });
            if(type)
            {
                _form.html.append("<input type='hidden' name='type' value='"+type+"' />");
            }
            return _form.html;
        };

        _form.generatePreviewRows = function(json_field,field_data) {

            var generateRow = function() {

                var cls_name = lowerNoSpace(json_field.name);
                var row = getTemplate('variation-preview-row-content-template');
                row.find('.preview-variant-name').html(json_field.name);
                row.find('.variants').addClass(cls_name).attr('variant_cls',cls_name);
                if(field_data)
                {
                    $.each(field_data,function(index,value) {
                        var input = $("<input type='hidden' value='"+value+"' name='"+json_field.name+"'  />");
                        row.find('.variants.'+cls_name).append(input);
                    });
                }
                return row;
            }

            var row_holder = getTemplate('variation-preview-row-template');
            row_holder.append(generateRow());

            return row_holder;

        };

        _form.generateFields = function(json_field, field_data) {

            var generateInput = function(selected_val) {
                var _input = {};

                switch(json_field.type)
                {
                    case "select":
                        _input = _form.generateSelect(json_field, selected_val);
                        break;
                    case "catalogLookup":
                        _input = _form.generateCatalogLookup(json_field, selected_val);
                        break;
                    default:
                        return "";
                }

                var input = getTemplate('variation-form-input-box-template');
                input.find('.input-holder').html(_input);
                input.find('.variation-box-name').html(json_field.name);
                return input;
            }

            var input_holder = getTemplate('variation-form-input-box-wrapper-template');

            if (field_data.length > 0) {
                for (var i=0; i<field_data.length; i++) {
                    input_holder.append(generateInput(field_data[i]));
                }
            } else {
                input_holder.append(generateInput());
            }

            return input_holder;
        };

        _form.generateCatalogLookup = function(json_field, selected_val) {
            var textInput = $("<input type='text' />").attr('name', json_field.name);

            // textInput.on('keyup', function(){ })

            var defaultText = selected_val || json_field.text;

            if('text' in json_field) {
                textInput.val(defaultText);
            }

            return textInput;
        }

        _form.generateSelect = function(json_field,selected_val) {

            var select = $("<select class='select2'><select>").attr('name', json_field.name);

            if(json_field.mandatory == "true") {
                select.addClass('mandatory');
            }

            if (json_field.dataSource) {
                var values = _lab.getDataSource(json_field.name);
            } else {
                var values = json_field.values;
            }

            if('text' in json_field) {
                select.append("<option value='-1'>" + json_field.text + "</option>");
            }

            if (json_field.dataType == 'values') {
                for (var i=0;i<values.length;i++) {
                    var val = values[i];
                    var option = $("<option></option>").val(val.val).text(val.text);
                    if(selected_val)
                    {
                        if(selected_val == val.val) option.attr('selected','selected');
                    }
                    select.append(option);
                }
            }

            if(json_field.dataType == 'range') //numric only
            {
                var range_array = values.split('-');
                var range_from = (range_array[0] * 1);
                var range_to = (range_array[1] * 1);
                var steps = (json_field.steps || 1) * 1;
                while(range_from < range_to)
                {
                    var option_text = range_from;
                    if(parseInt(steps) == steps) //int
                    {
                        option_text = option_text < 10 ? '0'+option_text : option_text;
                    }
                    var option = $("<option></option>").val(range_from).text(option_text);
                    if(selected_val)
                    {
                        if(selected_val == range_from) option.attr('selected','selected');
                    }
                    select.append(option);
                    range_from += steps;
                    if(steps < 1) range_from = parseFloat(range_from.toFixed(2));
                }

            }
            return select;
        }
        return _form;
    };
    return _lab;
})();
