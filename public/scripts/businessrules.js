
var businessAdmin = (function(){
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var max_description_length = 7;
    var _this = this;
    _this.inProgress = false;
    _this.ExcludedView = 0;
    _this.listTypes = {};
    _this.productTypes = {};
    _this.exclusion = '';

    /* Utils  */
    var getTemplate = function(template_id) {
        return $('#templates #'+template_id).clone().attr('id','');
    };

    var upperFirst = function(str) {

        return str[0].toUpperCase() + str.slice(1);
    }

    var createRndId = function(_prefix) {
        var prefix = _prefix || "var_";
        return prefix + parseInt(new Date().getTime() / 1000, 10)  + Math.floor(Math.random() * 0x75bcd15) + 1;
    }

    _this.init = function(listData, listTypes, productTypes, exclusion) {

        if(listTypes) {
            _this.listTypes = listTypes;
        }

        if(productTypes) {
            _this.productTypes = productTypes;
        }

        if(exclusion) {
            _this.exclusion = exclusion;
        }

        if(listData) {
            for (var i=0; i<listData.length; i++) {
                _this.addBox(listData[i]);
            }
        }
     }

    _this.expandColapse = function(obj) {
        var list_id = $(obj).closest('tr')[0].id.replace("list-contents-", "").replace("list-id-", "");

        $('#list-id-' + list_id).find('.list-content-toggle').children().first().toggleClass('fa-plus-square-o fa-minus-square-o');
        $('#list-contents-' + list_id).toggle();

        var expanded = $('#list-contents-' + list_id).is(":visible");

        if (expanded) {
            _this.initializeIframe(list_id);
        }
    }

    // used to reset the drop down list
    _this.resetOptions = function(obj) {
        var parent = $(obj).parents('tr');
        list_id = parent[0].id.replace("list-contents-", "");

        var list_type = $('#list-id-' + list_id).find('.list-type').text().toLowerCase();
        // reset list type
        $('#list-contents-' + list_id).find("select[name='list-type']").val(list_type);

        var list_product_type = $('#list-id-' + list_id).find('.list-product-type').text();

        $.each(_this.productTypes, function(index, value){
            if (list_product_type == value) {
                $('#list-contents-' + list_id).find("select[name='list-product-type']").val(index);
            }
        });

    }

    _this.addNewList = function(e) {
        var list_type = $(e).text().toLowerCase();
        var data = {
            name: '',
            description: '',
            product_type: 'product',
            list_type: list_type,
            updated: '',
            published: '',
            records_num: '0',
            status: 'off',
            excluded: ''
        }

        if ($('#list-id-new').length == 0) {
            this.addBox(data);
        }
    }

    _this.addBox = function(data) {

        if(data) {
            var dirtyClass = ''; // class name for dirty
            var id = (data.id) ? data.id : 'new';

            data.description = data.description ? data.description : '';

            // css trick to activate the off span
            var checked = (data.status == 'on') ? '' : 'hide';
            var unchecked = (data.status == 'on') ? 'hide' : '';

            data.records_num = (!data.records_num) ? '0' : data.records_num;
            data.published = (!data.published) ? '' : data.published;
            data.updated = (!data.updated) ? '' : data.updated;

            listTypeOptions = '';
            productTypesOptions = '';

            $.each(_this.productTypes, function(index, value){
                var selected = (data.product_type == index) ? 'selected' : '';
                productTypesOptions += '<option value="' + index + '" ' + selected + '>' + value + '</option>';
            });

            $.each(_this.listTypes, function(index, value){
                var selected = (data.list_type == _this.listTypes[index].id) ? 'selected' : '';
                listTypeOptions += '<option value="' + _this.listTypes[index].id + '" ' + selected + '>' + _this.listTypes[index].description + '</option>';
            });

            $.each(_this.listTypes, function(index, value){
                if (data.list_type == _this.listTypes[index].id) {
                    data.list_type = _this.listTypes[index].description;
                }
            });

            data.product_type = _this.productTypes[data.product_type];

            if (data.dirty != '0') {
                dirtyClass = 'dirty-list';
            }

            var html = ' \
                <tr id="list-id-' + id + '" class="' + dirtyClass + '"> \
                    <td style="vertical-align:middle"><span class="list-name">' + data.name + '</span></td> \
                    <td style="vertical-align:middle"><span class="list-type">' + data.list_type + '</span></td> \
                    <td style="vertical-align:middle"><span class="records-number">' + data.records_num + '</span></td> \
                    <td style="vertical-align:middle"><span class="last-published">' + data.published + '</span></td> \
                    <td style="vertical-align:middle">' + data.updated + '</td> \
                    <td style="vertical-align:middle"><span class="list-product-type">' + data.product_type + '</span></td> \
                    <td style="vertical-align:middle" align="center"> \
                        <a href="javascript:void(0);" class="list-content-toggle"><i class="fa fa-plus-square-o"></i></a> \
                    </td> \
                </tr> \
                <tr id="list-contents-' + id + '" class="' + dirtyClass + '" style="display:none">\
                    <td colspan="7">\
                    <div class="list-content-inner" style="width:95%">\
                    <div class="row btns-row" style="margin-bottom:10px;"> \
                        <div class="col-md-6"> \
                            <span class="list-description">' + data.description + '</span> \
                        </div> \
                        <div class="col-md-6"> \
                            <div class="pull-right"> \
                                <button onclick="businessAdmin.runUploader(this)" class="btn btn-default">Import</button> \
                                <button onclick="businessAdmin.exportList(this)" class="btn btn-default">Export</button> \
                                <div class="btn-group">\
                                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" onclick="businessAdmin.resetOptions(this)">More Actions&nbsp;&nbsp;<i class="fa fa-caret-down"></i></button>\
                                     <div class="dropdown-menu dropdown-menu-right" style="padding-top:15px;padding-left:20px;padding-right:20px;">\
                                        <div style="width:320px">\
                                            <div class="form-horizontal">\
                                              <div class="form-group row">\
                                                <label class="col-sm-6 form-control-label" style="font-weight:normal">Change List Type</label>\
                                                <div class="col-sm-6">\
                                                    <select class="form-control" onchange="businessAdmin.saveBox(this);" name="list-type">\
                                                    ' + listTypeOptions + ' \
                                                    </select>\
                                                </div>\
                                              </div>\
                                              <div class="form-group row">\
                                                <label class="col-sm-6 form-control-label" style="font-weight:normal">Change Product Type</label>\
                                                <div class="col-sm-6">\
                                                    <select class="form-control" onchange="businessAdmin.saveBox(this);" name="list-product-type">\
                                                    ' + productTypesOptions + ' \
                                                    </select>\
                                                </div>\
                                              </div>\
                                              <div class="form-group row">\
                                                <label for="list-type" class="col-sm-6 form-control-label" style="font-weight:normal">Permanently Clear List</label>\
                                                <div class="col-sm-6">\
                                                    <button onclick="businessAdmin.clearList(this)" class="btn btn-danger btn-block">Clear List</button>\
                                                </div>\
                                              </div>\
                                              <div class="form-group row">\
                                                <label for="list-type" class="col-sm-6 form-control-label" style="font-weight:normal">Permanently Delete List</label>\
                                                <div class="col-sm-6">\
                                                    <button onclick="businessAdmin.deleteBox(this)" class="btn btn-danger btn-block">Delete List</button>\
                                                </div>\
                                              </div>\
                                            </div>\
                                        </div>\
                                    </div>\
                                </div>\
                                <button onclick="businessAdmin.publishBox(this)" class="btn btn-primary publish">Publish</button> \
                            </div> \
                        </div> \
                    </div> \
                    <div class="simplegrid-full"> \
                        <iframe style="width: 100%; height: 466px;" scrolling="no" allowtransparency="true" frameborder="0" class="list-editor" src=""></iframe> \
                    </div> \
                    <div class="simplegrid-full list-footer"> \
                        <button onclick="businessAdmin.addListItem(this)" class="btn btn-primary">Add New Item</button> \
                    </div>\
                    </div> \
                    </td> \
                </tr>\
            ';

            $('#business-rules tr:last').after(html);

            $('#list-id-' + id).find('.list-name').editable({
                type: 'text',
                clear: false,
                pk: 1,
                url: "/lists/save",
                name: 'name',
                emptytext: 'Click here to enter list name',
                placeholder: 'List name',
                unsavedclass: null,
                params: function(params) {
                    var parent = $(this).parents('tr');
                    var list_id = parent[0].id.replace("list-id-", "");
                    var list_type = $(parent).find('.list-type').text();
                    var product_type = $(parent).find('.list-product-type').text();
                    var list_content = $("#list-contents-" + list_id);
                    var description = list_content.find('.list-description');

                    $.each(_this.productTypes, function(index, value){
                        if (product_type == value) {
                            product_type = index;
                            return;
                        }
                    });

                    var status = $(parent).find('button.btn-status:visible').text().trim().toLowerCase();

                    if (list_id == 'new') {
                        if (!description.hasClass('editable-empty')) {
                            params['description'] = description.text();
                        }
                        params['list-type'] = list_type.toLowerCase();
                        params['product-type'] = product_type;
                        params['status'] = status;
                        params['excluded'] = _this.exclusion;
                    } else {
                        params['list-id'] = list_id;
                    }
                    return params;
                },
                success: function(response, newValue) {
                    if(response.status != "error") {
                        _this.setDirty(id);
                        _this.notify("success", response.message);

                        if ('new_id' in response) {
                            // set description ajax options
                            $('#list-contents-new').find('.list-description').editable('option', 'url', '/lists/save');
                            $('#list-contents-new').find('.list-description').editable('option', 'pk', 1);

                            $('#list-id-new').attr("id", 'list-id-' + response.new_id);
                            $('#list-contents-new').attr("id", 'list-contents-' + response.new_id);
                        }
                    } else {
                        _this.notify("error", response.message);
                    }
                },
                error: function(a, b, c) {
                    // basically the csrf token expired
                    location.reload();
                },
                validate: function(v) {
                    /*
                    TODO
                    if (confirm("Are you sure you would like to change the list's name?"))
                    */
                    if(!v) return 'List name is required!';
                }
            });

            // attach toggle functionality to display list contents
            $('#list-id-' + id).find('.list-content-toggle').bind('click', function() { _this.expandColapse(this); });

            var optsDescription = {
                type: 'text',
                clear: false,
                name: 'description',
                emptytext: 'Click here to enter description',
                placeholder: 'Description',
                unsavedclass: null,
                params: function(params) {
                    var parent = $(this).parents('tr');
                    var list_id = parent[0].id.replace("list-contents-", "");
                    params['list-id'] = list_id;
                    return params
                }
            }

            if (id !== 'new') {
                optsDescription['pk'] = 1;
                optsDescription['url'] = '/lists/save';
            }

            $('#list-contents-' + id).find('.list-description').editable(optsDescription);
        }

        /*
        TODO
        $('html, body').animate({
            scrollTop: $('.lists-container .list-box').last().offset().top
        });
        */
    }

    _this.syncInput = function(obj) {
        var parent = $(obj).parents('tr');
        list_id = parent[0].id.replace("list-contents-", "");

        var classname = '.' + $(obj).attr('name');

        var txt = $(obj).find('option:selected').text();

        // find the element with the class name
        $('#list-id-' + list_id).find(classname).text(txt);
    }

    _this.saveBox = function(obj) {
        var parent = $(obj).parents('tr');
        var name = $(obj).attr('name');
        var selected = $(obj).find('option:selected');

        var params = {};

        list_id = parent[0].id.replace("list-contents-", "");

        params['list-id'] = list_id;

        if(name=='list-type') {
            params['list-type'] = selected.val();
        }

        if(name=='list-product-type') {
            params['product-type'] = selected.val();
        }

        var _save = function() {
            $.ajax({
                url: "/lists/save" ,
                type: "POST",
                data: $.param( params, true ),
                success: function(data){
                    if(data.status != "error")
                    {
                        _this.setDirty(list_id);
                        notify("success", data.message);
                        _this.syncInput(obj)
                    }
                    else
                    {
                        notify("error", data.message);
                    }
                },
                error: function(){
                    notify("error","Connection Error");
                }
            });
        }

        if (list_id == "new") {
            _this.syncInput(obj)
        } else {
            _save();
        }
    }


    _this.deleteBox = function(obj) {
        var listcontents = $(obj).parents('tr');
        var list_id = listcontents[0].id.replace("list-contents-", "");
        var list = $('#list-id-' + list_id);
        var list_name = list.find('.list-name').text();
        var list_status = (list.find('input[type=checkbox]').is(':checked')) ? 'on' : 'off';
        // var list_status = 'off';

        if(list_status != "off") {
            notify("error","Please switch list off before deleting");
            return false;
        }

        var remove = function() {
            listcontents.remove();
            list.remove();
            notify("success","List removed");
        }

        if(list_id != 'new') {
            var _del = function() {
                $.ajax({
                    url: "/lists/remove",
                    type: "POST",
                    data: "list-id=" + list_id,
                    dataType: "json",
                    success: function(data){
                        notify("success","List removed");
                        remove();
                    },
                    error: function(){
                        notify("error","Connection Error");
                    }
                });
            }

            if (confirm("Please confirm that you wish to delete list " + upperFirst(list_name))) {
                _del();
            }
        }
        else remove();
    }

    _this.clearList = function(obj) {
        var listcontents = $(obj).parents('tr');
        var list_id = listcontents[0].id.replace("list-contents-", "");
        var list = $('#list-id-' + list_id);
        var list_name = list.find('.list-name').text();

        if(list_id == 'new') {
            notify("error","Please save list first");
            return false;
        }

        var _clear = function() {
            $.ajax({
                url: "/listitems/clear",
                dataType: "json",
                type: "POST",
                data: "list-id="+list_id,
                success: function(data){
                    if(data.status != "error")
                    {
                        // TODO
                        _this.setDirty(list_id);
                        notify("success",data.message);
                    }
                    else
                    {
                        notify("error",data.message);
                    }
                    _this.initializeIframe(list_id);
                    _this.setRecords(list_id, 0);
                },
                error: function(){
                    notify("error","Connection Error");
                }
            });
        }

        if (confirm("Please confirm that you wish to clear the list " + list_name)) {
            _clear();
        }

        return false;
    }

    _this.publishBox = function(obj) {
        var listcontents = $(obj).parents('tr');
        var list_id = listcontents[0].id.replace("list-contents-", "");
        var list = $('#list-id-' + list_id);

        var params = {
            'list-id' : list_id,
        }

        if(list_id == 'new') {
            notify("error","Please save list first");
            return false;
        }

        var _publish = function() {
            $.ajax({
                url: "/lists/publish" ,
                dataType: "json",
                type: "POST",
                data: $.param( params, true ),
                success: function(data){
                    if (data.status != "error") {
                        notify("success", data.message);
                        list.find('.last-published').text(data.published);

                        // TODO
                        _this.setUnDirty(list_id);
                        // Once the list is published - it is no longer marked as 'dirty'
                        // parentRow.removeClass('dirty-lists');
                    } else {
                        notify("error", data.message);
                    }
                },
                error: function(){
                    notify("error","Connection Error");
                }
            });
        }

        if (confirm("Are you sure you would like to publish changes to production?\n Changes will be reflected in 10 minutes")) {
            _publish();
        }
    }

    _this.searchAll = function() {
        var search_q = $('#search-all').val();

        if(search_q.length>0){
            collapseAll();
        }
        else{
            return;
        }

        var params = {
            'search_q': search_q ,
        }
            $.ajax({
                url: "/listitems/search-all" ,
                data: $.param( params, true ),
                dataType:'json',
                 success: function(data) {
                    if (data.status != "error") {
                        if (data['result'].length <1) {
                            notify("error", "No results");
                        return;
                    } else {
                        for (i = 0; i < data['result'].length; i++) {
                            var list_id = data['result'][i]['list_id'];
                            $('#list-id-' + list_id).find('.list-content-toggle').children().first().toggleClass('fa-plus-square-o fa-minus-square-o');
                            $('#list-contents-' + list_id).toggle();
                            _this.initializeIframe(list_id, search_q);
                        }
                    }
                } else {

                }
            },
                error: function(){
                    notify("error","Connection Error");
                }
            });
     }

    _this.collapseAll = function() {
        $('#business-rules tr').filter(function(){
            return this.id.match(/list-id-/)
        }).each(function(index, obj){
            var list_id = obj.id.replace("list-id-", "")
            var expanded = $('#list-contents-' + list_id).is(":visible");
            if (expanded) {
                _this.expandColapse(obj);
            }
        });
    };

    _this.expandByListId = function (list_id) {
        $('#list-id-' + list_id).find('.list-content-toggle').children().first().toggleClass('fa-plus-square-o fa-minus-square-o');
        $('#list-contents-' + list_id).show();
    }

    _this.filterByProductType = function(obj) {
        var selected = $(obj).find('option:selected').text();

        $('#business-rules tr').filter(function(){
            return this.id.match(/list-id-/)
        }).each(function(){
            var productType = $(this).find('span.list-product-type').text();
            var list_id = this.id.replace('list-id-','');
            if (selected == 'All') {
                $(this).show();
                return;
            }

            if (productType !== selected) {
                // console.log(productType, list_id);
                $(this).hide();

                if($('#list-contents-' + list_id).is(':visible')){
                    $('#list-contents-' + list_id).hide();
                }
            } else {
                $(this).show();
            }
        });
     }

    _this.initializeIframe = function(list_id, searchStr) {
        var list = $('#list-id-' + list_id)
        var list_contents = $('#list-contents-' + list_id)

        var ext_id = 'list-editor-' + list_id // parentRow.find('.list-editor').attr('id');

        var list_type = list.find('.list-type').text();

        params = {
            'list-id': list_id,
            'list_type': list_type,
            'ext_id': ext_id,
            'excluded': _this.exclusion,
            'search_q': searchStr
        }

        // /listitems?list-id={{ $list['id'] }}&list_type=flex1&search_q=
        var link =  "/listitems?" + $.param(params);

        list_contents.find('iframe.list-editor').attr('src', link);
    }

    _this.setRecords = function(list_id,records) {
        var list = $('#list-id-' + list_id);
        list.find('.records-number').text(records);
    }

    _this.exportList = function(obj) {
        var listcontents = $(obj).parents('tr');
        var list_id = listcontents[0].id.replace("list-contents-", "");
        var list = $('#list-id-' + list_id);

        if(list_id == 'new') {
            notify("error","Please save list first");
            return false;
        }

        var list_type = list.find('.list-type').text();

        var link =  "/listitems/export/?list-id=" + list_id + "&list_type=" + list_type;
        window.open(link, '_blank');
    }

    _this.runUploader = function(obj) {
        var listcontents = $(obj).parents('tr');
        var list_id = listcontents[0].id.replace("list-contents-", "");
        var list = $('#list-id-' + list_id);
        var list_type = list.find('.list-type').text().toLowerCase();
        var excluded = _this.exclusion;

        if(list_id == 'new') {
            notify("error","Please save list first");
            return false;
        }

        var template = $('#importModal');
        template.find("input[name='uploadedfile']").val("");
        template.find('input[name=list-id]').val(list_id);
        template.find('input[name=list-type]').val(list_type);
        template.find('input[name=excluded]').val(excluded);

        template.find('.uploader-container').html("<iframe id='uploader_frame' name='uploader_frame' onload='uploaderOnLoad(" + list_id + ")'></iframe>");

        template.modal();
    }


    _this.addListItem = function(obj) {
        var listcontents = $(obj).parents('tr');
        var list_id = listcontents[0].id.replace("list-contents-", "");
        var list = $('#list-id-' + list_id);
        var list_type = list.find('.list-type').text().toLowerCase();
        var list_name = list.find('.list-name').text();

        if(list_id == 'new') {
            notify("error","Please save list first");
            return false;
        }

        var template = $('#addNewModal');
        template.find("input[type='text']").val("");

        var list_type_display = list_type == 'sku' ? 'SKU' : upperFirst(list_type);
        template.find('.list-type-variable').attr('placeholder',list_type_display);
        template.find('input[name=list-type]').val(list_type);
        template.find('input[name=list-id]').val(list_id);
        var modalTitle = 'Add '+list_type_display + ' To List: \''+list_name + '\'';
        template.find('.modal-title').text(modalTitle);
        $('#addNewModal').modal({});
    }


    // inside IFRAME
    _this.saveListItem = function(form_data, callback, errCallback) {
        if( _this.inProgress) return false;
        _this.inProgress = true;
        $.ajax({
            url: "/listitems/save" ,
            dataType: "json",
            type: "POST",
            data: form_data,
            success: function(data){
                if(data.status != "error")
                {
                    var list_id = $.url('http://dummy.com?' + form_data).param('list-id');
                    parent.setDirty(list_id);
                    parent.notify("success",data.message);
                    if(typeof(callback) == "function")
                    {
                        callback();
                    }
                }
                else
                {
                    parent.notify("error", data.message);
                    if(typeof(errCallback) == "function")
                    {
                        errCallback();
                    }
                }
                 _this.inProgress = false;
            },
            error: function(err, data){
                // console.log(err);
                parent.notify("error","Connection Error");
                _this.inProgress = false;
            }
        });

    }

    _this.deleteListItem = function(item_id, list_id, callback) {
        if( _this.inProgress) return false;

        _this.inProgress = true;

        $.ajax({
            url: "/listitems/delete" ,
            dataType: "json",
            type: "POST",
            data: "item_id="+item_id+"&list_id="+list_id,
            success: function(data){
                if(data.status != "error")
                {
                    parent.setDirty(list_id)
                    parent.notify("success", data.message);

                    _this.initializeIframe(list_id);

                    if(typeof(callback) == "function")
                    {
                        callback();
                    }
                }
                else
                {
                    parent.notify("error", data.message);
                }
                 _this.inProgress = false;
            },
            error: function(){
                parent.notify("error","Connection Error");
                _this.inProgress = false;
            }
        });
    }


    _this.saveNewListItem = function(list_id) {
        var parentForm = $('.new-item-form:visible').eq(0);
        var form_data = parentForm.serialize();

        $.ajax({
            url: "/listitems/save" ,
            dataType: "json",
            type: "POST",
            data: form_data,
            success: function(data){
                if(data.status != "error")
                {
                    parent.notify("success",data.message);
                    _this.initializeIframe(list_id);

                    _this.setDirty(list_id);
                    $('#addNewModal').modal('toggle')
                }
                else
                {
                    parent.notify("error",data.message);
                }
            },
            error: function(){
                parent.notify("error","Connection Error");
            }
        });

    }

    _this.setUnDirty = function(list_id){
        $('#list-id-' + list_id).removeClass('dirty-list');
        $('#list-contents-' + list_id).removeClass('dirty-list');
        window.parent.onbeforeunload = null;
    }

    _this.setDirty = function(list_id) {
        console.log(list_id);
        $('#list-id-' + list_id).addClass('dirty-list');
        $('#list-contents-' + list_id).addClass('dirty-list');

        window.parent.onbeforeunload = function() {
            return 'There are unpublished changes. are you sure you want to leave?' ;
        };
    }

    _this.changeStatus = function(obj) {
        var list = $(obj).parents('tr');
        var list_id = list[0].id.replace("list-id-", "");
        var list_name = list.find('.list-name').text();

        // switch to new status
        var list_status = ($(obj).text().trim().toLowerCase() == 'on') ? 'off' : 'on';

        if (list_id == "new") {
            notify("error", "Please save list first");
        } else {
            var msg = 'Are you sure you want to turn ' + list_status.toUpperCase() + ' list ' + list_name + '?';
            if (confirm(msg)) {
                $.ajax({
                    url: "/lists/change-status",
                    dataType: "json",
                    type: "POST",
                    data: "list_id=" + list_id + "&list_status=" + list_status,
                    success: function(data){
                        if(data.status != "error")
                        {
                            notify("success", data.message);
                            // toggle the visibility of the buttons
                            list.find('.rule-status button').each(function(){
                                $(this).toggleClass('hide');
                            });
                            _this.setDirty(list_id);
                        }
                        else
                        {
                            notify("error", data.message);
                        }
                    },
                    error: function(){
                        notify("error","Connection Error");
                    }
                });

            }
        }
    };

    _this.showUploadLogger = function(fileInput) {

        if(fileInput.value.substr(fileInput.value.length -4) != ".csv")
        {
            notify("error","File extension should be csv only");
            return false;
        }
        showLoading();
        fileInput.form.submit();
    	$('#uploader_frame').show();
        $('.upload-form-dialog:visible .note').slideUp();

    	/*
        $("#uploader_frame" ).animate({
            'right':'-2px'
          }, 1000);
		*/
    }

    return _this;

})();
