var notify = function(type, msg) {
    $('#notifier').removeClass()
         .addClass(type)
         .html(msg)
         .fadeIn("fast",function() {
            setTimeout(hide_errors,5000)
          });
}

var logMessages = [];

var solutionArr = [
    "Conversion Uplift",
    "Profit Optimization",
    "User Spend Increase",
    "Loyalty and Retention",
    "Omni-Channel Support",
    "PLC Management"
];

solutionArr.getKeyByValue = function( value ) {
    for( var prop in this ) {
        if( this.hasOwnProperty( prop ) ) {
            if( this[ prop ] === value )
                return prop;
        }
    }
};

var hide_errors = function() {
    $('#notifier').fadeOut('slow');
}

function objToCSV(objArray) {
    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
    var str = '';

    for (var i = 0; i < array.length; i++) {
        var line = '';
        for (var index in array[i]) {
            if (line != '') line += ','

            line += array[i][index];
        }

        str += line + '\r\n';
    }

    return str;
}

function typeOnChange(el) {
    $el = $(el);
    if($el.find('option:selected').val() == '1') {
        $el.parent().next().show();
    } else {
        $el.parent().next().hide();
    }
}

function validateOperationName(el) {
    var alphaNumericTest = new RegExp(/^[a-z0-9]+$/i);
    var operationName = $(el).val();

    if (!alphaNumericTest.test(operationName)) {
        notify("error", 'Operation name must contain only letters or numbers');
        el.focus();
        return;
    }

    if (operationName !== el.defaultValue) {
        if(el.defaultValue == '') {
            logMessages.push('New operation added ' + operationName);
        } else {
            logMessages.push('Operation name changed to ' + operationName + ' from ' + el.defaultValue)
        }
    }

    el.defaultValue = operationName;
}

function deleteOperation(el) {
    var operationName = $(el).parent().find('input[name=operation_name]').val();
    if(confirm('Are you sure you want to delete this operation?')) {
        logMessages.push('Deleted operation ' + operationName);
        $(el).closest('div.row').remove();
    }
}

function editAllSampleGroups(el) {
    var $table = $(el).closest('table')
    var $tableRows = $table.find('tbody > tr');
    var sampleGroupData = [];

    $table.find('button[name=edit-all]').hide();
    $table.find('button[name=save-all]').show();

    var newNumberOfRows = 10 - $tableRows.length;

    for (var i = $tableRows.length - 1; i >= 0; i--) {
        var row = $($tableRows[i]);

        var id = row.find('td[name=sg_id]').html();
        var description = row.find('td[name=sg_description]').html();
        var size = row.find('td[name=sg_size]').html();
        var isControlGroup = row.find('td[name=sg_is_control]').html();

        addEditableSampleGroupRow(row, false, id, description, size, isControlGroup);

        sampleGroupData.push({
            id: id,
            description: description,
            size: size,
            isControlGroup: isControlGroup
        })

        row.remove();
    }

    $table.find('input[name=sample-group-data]').val(JSON.stringify(sampleGroupData));

    while (newNumberOfRows--) {
        addEditableSampleGroupRow($table, true);
    }
}

function saveAllSampleGroups(el) {
    var tableRows = $(el).closest('table').find('tbody > tr');

    var rowsValid = true;
    var sumSizes = 0;
    var sumControlGroups = 0;
    var errorMessage = '';

    tableRows.each(function(i, tr){
        var rowValid = validateSampleGroupRow( $(tableRows[i]) );
        errorMessage = rowValid.message;
        sumSizes += (parseInt(rowValid.rowData.size) || 0);
        sumControlGroups += (rowValid.rowData.is_control_group.toLowerCase() == 'yes') ? 1 : 0;
        return rowsValid = rowValid.valid; // this exits the each if false
    });

    if (!rowsValid) {
        notify("error", errorMessage);
        return false;
    }

    // check if the sum is greater than 100%
    if (sumSizes > 100) {
        notify("error", "Sum of sizes are more than 100%, please review.");
        return false;
    }

    // more than one control group
    if (sumControlGroups > 1) {
        notify("error", "Too many control goups.");
        return false;
    }

    if (rowsValid) {
        tableRows.each(function(i, tr){
            saveRow( $(tableRows[i]) );
        });

        $(el).closest('table').find('button[name=edit-all]').show();
        $(el).closest('table').find('button[name=save-all]').hide();
    }

    var $tableRows = $(el).closest('table').find('tbody > tr');
    var sampleGroupData = [];

    for (var i = $tableRows.length - 1; i >= 0; i--) {
        var row = $($tableRows[i]);

        var id = row.find('td[name=sg_id]').html();
        var description = row.find('td[name=sg_description]').html();
        var size = row.find('td[name=sg_size]').html();
        var isControlGroup = row.find('td[name=sg_is_control]').html();

        sampleGroupData.push({
            id: id,
            description: description,
            size: size,
            isControlGroup: isControlGroup
        });
    }

    var previousSampleData = $(el).closest('table').find('input[name=sample-group-data]').val();
    var currentSampleData = JSON.stringify(sampleGroupData);

    if (previousSampleData !== currentSampleData) {
        var operationName = $(el).closest('table').parent().parent().parent().parent().parent().find('input[name=operation_name]').val();
        var sampleDataCSV = objToCSV(currentSampleData);
        logMessages.push('Operation ' + operationName + ' sample group info changed :<br/>' + sampleDataCSV);
    }
}

function validateSampleGroupRow(tr) {
    var alphaNumericTest = new RegExp(/^[a-z0-9]+$/i);

    var rowData = {
        id: tr.find('input[name=sg_id]').val().trim(),
        size: tr.find('input[name=sg_size]').val().trim(),
        description: tr.find('input[name=sg_description]').val().trim(),
        is_control_group: (tr.find(':checkbox').is(':checked')) ? 'Yes' : 'No'
    }

    var rowValidation = {
        valid: true,
        message: '',
        rowData: rowData
    }

    if (!rowData.size && !rowData.description && !rowData.id) {
        return rowValidation;
    }

    if (rowData.id) {
        if (!alphaNumericTest.test(rowData.id)) {
            rowValidation.valid = false;
            rowValidation.message = 'Group ID can only contain letters and numbers';
            return rowValidation;
        }
    } else {
        rowValidation.valid = false;
        rowValidation.message = 'Group ID cannot be empty and is required';
        return rowValidation;
    }

    if (rowData.size && isNaN(rowData.size)) {
        rowValidation.valid = false;
        rowValidation.message = 'Size is not valid number';
        return rowValidation;
    }

    if(rowData.size < 1) {
        rowValidation.valid = false;
        rowValidation.message = 'Size must be greater than 0';
        return rowValidation;
    }

    return rowValidation;
}

function saveRow(tr) {
    var id = tr.find('input[name=sg_id]').val().trim();
    var size = tr.find('input[name=sg_size]').val().trim();
    var description = tr.find('input[name=sg_description]').val().trim();
    var is_control_group = (tr.find(':checkbox').is(':checked')) ? 'Yes' : 'No'

    // update sizes if they changed
    /*
    var sizes = table.find('td[name=sg_size]');
    sizes.each(function(e, index){
        if ($(index).html() != $(index).attr('original-value')) {
            $(index).attr('original-value', $(index).html());
        }
    })
    */

    if (id || size || name) {
        addExistingSampleGroup(tr, id, description, size, is_control_group);
    }

    tr.remove();
}

/*
function adjustSize(el) {
    var table = $(el).closest('table');
    var sizes = table.find('td[name=sg_size]');
    var value = Number($(el).val());

    var currentTotal = 0;

    sizes.each(function(e, index){
        currentTotal = currentTotal + Number($(index).html());
    })

    var leftOver = (currentTotal + value) - 100;

    sizes.each(function(e, index){
        var n = Number($(index).html());
        var original = Number($(index).attr('original-size'));

        var rr = (n / currentTotal) // relative reduction

        if (leftOver > 0) {
            n = n - (rr * leftOver)
        } else {
            n = n + (rr * -leftOver)
        }

        $(index).html(parseFloat(n.toFixed(1)));
    })
}
*/

function addEditableSampleGroupRow(el, isNew, id, description, size, isControlGroup) { // editable sample group row
    var table = $(el).closest('table');

    var template = $('#editable-sample-group-template').html();
    var editableRow = $(template);

    if (isNew) {
        /*
        var sizes = table.find('td[name=sg_size]');
        sizes.each(function(e, index){
            size = size - $(index).html();
        })
        */

        editableRow.find('input[name=sg_is]').val("new");
        table.find('tbody').append(template);
    } else {
        var idInput = editableRow.find('input[name=sg_id]')
        idInput.val(id);
        idInput.attr('original-value', id);

        var descriptionInput = editableRow.find('input[name=sg_description]')
        descriptionInput.val(description);
        descriptionInput.attr('original-value', description);

        var sizeInput = editableRow.find('input[name=sg_size]');
        sizeInput.val(size);
        sizeInput.attr('original-value', size);

        var checkbox = editableRow.find(':checkbox');
        if (isControlGroup == "Yes") {
            checkbox.attr('checked','checked');
        }

        if (el.get(0).nodeName.toLowerCase() === 'tbody') {
            el.append(editableRow);
        } else {
            el.after(editableRow);
        }
    }
}

function addExistingSampleGroup(el, id, description, size, isControlGroup) {
    var tmpl = $('#sample-group-template').html();
    var $row = $(tmpl);

    $row.find('td[name=sg_id]').html(id);
    $row.find('td[name=sg_description]').html(description);
    $row.find('td[name=sg_size]').html(size);
    $row.find('td[name=sg_is_control]').html(isControlGroup);

    if (el.get(0).nodeName.toLowerCase() === 'tbody') {
        $(el).append($row);
    } else {
        $(el).after($row);
    }
}


function operationStatusOnChange(el, logMessage) {
    // toggle hide button
    var buttons = $(el).parent().find('button');
    var operationName = $(el).parent().find('input[name=operation_name]').val();

    var currentState = $(el).text();

    var msg = 'from ' + currentState + ' to ';

    for (var i = 0; i < buttons.length; i++) {
        if ($(buttons[i]).text() != currentState) {
            msg += $(buttons[i]).text();
        }

        $(buttons[i]).toggleClass('hide');
    }

    // get name
    if (logMessage) {
        logMessages.push('operation ' + operationName + ' changed status ' + msg);
    }
}

function onChangeBusinessLists (e, listType, nameInput) {
        var operationName = nameInput.val();

        if (e.added) {
            logMessages.push('Operation ' + operationName + ' added ' + e.added.text + ' to ' + listType)
        }

        if (e.removed) {
            logMessages.push('Operation ' + operationName + ' removed ' + e.removed.text + ' to ' + listType)
        }
}

function addNewOperation() {
    var newOperation = $('#new-operation').html();
    var $newOperation = $(newOperation);

    var newId = getNewOperationId();
    $newOperation.find('input[name=operation_id]').val(newId);

    $operationNameInput = $newOperation.find('input[name=operation_name]');

    $sampleGroupTable = $newOperation.find('table[name=sample-group]');
    for(var i=0; i<10; i++) {
        addEditableSampleGroupRow($sampleGroupTable, true);
    }

    $newOperation.find('button[name=edit-all]').hide();

    typeOnChange($newOperation.find('select[name=type]'));

    var bl = JSON.parse(JSON.stringify(businessLists));
    for(index in bl) {
        $newOperation.find('select[name=business-lists]').append($('<option></option>').attr('value', bl[index]).text(bl[index]));
        $newOperation.find('select[name=business-lists-excluded]').append($('<option></option>').attr('value', bl[index]).text(bl[index]));
    }

    $newOperation.find('select[name=business-lists]').on("change", function(e) { onChangeBusinessLists(e, 'business list', $operationNameInput) });
    $newOperation.find('select[name=business-lists-excluded]').on("change", function(e) { onChangeBusinessLists(e, 'business list exclusion', $operationNameInput) });

    $newOperation.find('select[name=business-lists]').select2({placeholder : ''});
    $newOperation.find('select[name=business-lists-excluded]').select2({placeholder : ''});

    $newOperation.find('div[name=sample-groups-container]').toggleClass('in');
    $newOperation.find('button[data-toggle=collapse]').attr('data-target', '#sample-groups-container-' + newId);
    $newOperation.find('div[name=sample-groups-container]').attr('id', 'sample-groups-container-' + newId);

    $('#operations-tool-buttons').after($newOperation);

    $newOperation.find('select[name=business-lists]').select2("container").find("ul.select2-choices").sortable({
        containment: 'parent',
        start: function() { $newOperation.select2("onSortStart"); },
        update: function() { $newOperation.select2("onSortEnd"); }
    });
    $($newOperation).find('.solution-select').show();
    $($newOperation).find('.solution-title').hide();
    $($newOperation).find('select[name=solution]').attr('class','solution-value form-control');
}

function addExistingOperation(operation) {
    var newOperation = $('#new-operation').html();
    var $newOperation = $(newOperation);

    $newOperation.find('input[name=operation_id]').val(operation.id);
    // $newOperation.find('input[name=operation_name]')
    var $operationName = $newOperation.find('input[name=operation_name]');
    $operationName.val(operation.name);
    $operationName.attr("value", operation.name);
    if(operation.solution != null){
        $newOperation.find('.solution-name').text(solutionArr[operation.solution]);
    }
    $sampleGroupTable = $newOperation.find('table[name=sample-group] > tbody');

    $newOperation.find('button[name=save-all]').hide();

    for(var i = 0; i < operation.split.length; i++) {
        // here we should set up the size using startRanges and endRanges
        var size = (operation.split[i].startRange == 1)
                    ? operation.split[i].endRange
                    : operation.split[i].endRange - operation.split[i-1].endRange;

        addExistingSampleGroup($sampleGroupTable,
            operation.split[i].id, operation.split[i].description,
            (Math.max(0, size) || ''), (operation.split[i].isControlGroup) ? 'Yes' : 'No');
    }

    var bl = JSON.parse(JSON.stringify(businessLists));

    if (operation.businessLists && operation.businessLists.length) {
        for(var i = 0; i < operation.businessLists.length; i++) {
            bl.splice(i, 0, bl.splice(bl.indexOf( operation.businessLists[i]), 1)[0]);
        }

        for(index in bl) {
            $newOperation.find('select[name=business-lists]').append($('<option></option>').attr('value', bl[index]).text(bl[index]));
        }

        for(var i = 0; i < operation.businessLists.length; i++) {
            $newOperation.find('select[name=business-lists] option[value="' + operation.businessLists[i] + '"]').attr('selected','selected');
        }
    } else {
        for(index in bl) {
            $newOperation.find('select[name=business-lists]').append($('<option></option>').attr('value', bl[index]).text(bl[index]));
        }
    }

    var bl = JSON.parse(JSON.stringify(businessLists));

    if (operation.businessListsExclusion && operation.businessListsExclusion.length) {
        for(var i = 0; i < operation.businessListsExclusion.length; i++) {
            bl.splice(i, 0, bl.splice(bl.indexOf( operation.businessListsExclusion[i]), 1)[0]);
        }

        for(index in bl) {
            $newOperation.find('select[name=business-lists-excluded]').append($('<option></option>').attr('value', bl[index]).text(bl[index]));
        }

        for(var i = 0; i < operation.businessListsExclusion.length; i++) {
            $newOperation.find('select[name=business-lists-excluded] option[value="' + operation.businessListsExclusion[i] + '"]').attr('selected','selected');
        }
    } else {
        for(index in bl) {
            $newOperation.find('select[name=business-lists-excluded]').append($('<option></option>').attr('value', bl[index]).text(bl[index]));
        }
    }

    // if status is active
    if (operation.status == '0') {
        operationStatusOnChange($newOperation.find('button[name=status_1]'));
    }

    $newOperation.find('select option[value="' + operation.type + '"]').attr("selected", "selected");
    typeOnChange($newOperation.find('select[name=type]'));

    $newOperation.find('select[name=business-lists]').select2({placeholder : ''});
    $newOperation.find('select[name=business-lists-excluded]').select2({placeholder : ''});

    $newOperation.find('select[name=business-lists]').on("change", function(e) { onChangeBusinessLists(e, 'business list', $operationName) });
    $newOperation.find('select[name=business-lists-excluded]').on("change", function(e) { onChangeBusinessLists(e, 'business list exclusion', $operationName) });

    $newOperation.find('button[data-toggle=collapse]').attr('data-target', '#sample-groups-container-' + operation.id);
    $newOperation.find('div[name=sample-groups-container]').attr('id', 'sample-groups-container-' + operation.id);

    $('#operations-tool-buttons').after($newOperation);

    $newOperation.find('select[name=business-lists]').select2("container").find("ul.select2-choices").sortable({
        containment: 'parent',
        start: function() { $newOperation.select2("onSortStart"); },
        update: function() { $newOperation.select2("onSortEnd"); }
    });
}

function getNewOperationId() {
    var existingIds = [];
    var allOperations = getAllOperations();

    // array of 30 numbers from 1 to 30
    var array = new Array(30)
        .join().split(',')
        .map(function(item, index){ return ++index;})

    // get which id's already exist out of 30
    $(allOperations).each(function(i, operation){
        existingIds.push(Number(operation.id));
    });

    // remove existing id's
    array = array.filter(function(item) { return existingIds.indexOf(item)  < 0 });

    // return first element in array
    return array[0];
}

function getAllOperations() {
    // traverse all these panels and construct json
    var panels = $('div[name=operation-panel]');
    var operations = [];

    panels.each(function(i, panel) {
        var operation = {};

        operation.name = $(panel).find('input[name=operation_name]').val();
        operation.id = $(panel).find('input[name=operation_id]').val();
        operation.sampleGroups = [];
        operation.businessLists = [];
        operation.businessListsExclusion = [];
        operation.type = $(panel).find('select[name=type]').val();
        operation.solution = $(panel).find('.solution-value').val() || solutionArr.getKeyByValue($(panel).find('.solution-name').text()) || 0; // solution-value for new operation, solution-name for republish of old operation, 0 for legacy: very old operations from when all solutions were conversion uplift (hence 0).

        var tableRows = $(panel).find('table>tbody>tr');
        tableRows.each(function(i, tableRow){
            var sampleGroup = {};
            sampleGroup.id = $(tableRow).find('td[name=sg_id]').html();
            sampleGroup.size = $(tableRow).find('td[name=sg_size]').html();
            sampleGroup.description = $(tableRow).find('td[name=sg_description]').html();
            sampleGroup.isControlGroup = $(tableRow).find('td[name=sg_is_control]').html();
            operation.sampleGroups.push(sampleGroup);
        });

        // get selected business lists
        $($(panel).find('select[name=business-lists]').select2('data')).each(function(i, el){
            operation.businessLists.push(el.text);
        });

        // get selected business lists
        $($(panel).find('select[name=business-lists-excluded]').select2('data')).each(function(i, el){
            operation.businessListsExclusion.push(el.text);
        });

        // get operation status
        operation.status = ( $(panel).find('button[name=status_0]').is(':visible') ) ? 0 : 1;

        operations.push(operation);
    });

    return operations;
}

function publishChanges() {
    // if there are any panels groups that are being edited or added
    // throw message
    var operationInfo = {};
    operationInfo.operations = getAllOperations();
    operationInfo.logMessages = logMessages;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: publishChangesUrl,
        dataType: "json",
        type: "post",
        data: JSON.stringify(operationInfo),
        success: function(data){
            notify("success", "Updated Successfully");
            logMessages = [];
            // update

        },
        error: function(){
            notify("error", "Connection Error");
        }
    });
}
