<script src="{{ url('scripts/bootstrap-editable.js') }}"></script>
<script src="{{ url('scripts/retailer-validation.js') }}"></script>
<script src="{{ url('scripts/smartpack-lists.js') }}"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<script type="text/javascript">
window.addEventListener('sessionchanged', function (e) {
    location.reload();
});

var csrf_token = '{{ csrf_token() }}';

$('#add-new-list').find('a').each(function(index) {
    $(this).on('click', null, function() {
        smartpackAdmin.addNewList(this);
    });
});

$('.dropdown-menu').mouseleave(function () {
    $(".myFakeClass").dropdown('toggle');
});

$(document).ready(function() {

 // $('tbody').addClass("DragMe");

 //                    $('.DragMe').sortable({
 //                        disabled: false,
 //                        axis: 'y',
 //                        items: "> tr:not(:first)",
 //                        forceHelperSize: true,
 //                        update: function (event, ui) {
 //                            var newpos = ui.item.index();
 //                            // var MovedItem
 //                            // var RefID = $('tr').find('td:first').html();

 //                        console.log("Position " + newpos);



                            

 //                        smartpackAdmin.updateListIndex(this,newpos);

                          

 //                        }
 //                    }).disableSelection();

var fixHelperModified = function(e, tr) {
    var $originals = tr.children();
    var $helper = tr.clone();
    $helper.children().each(function(index) {
        $(this).width($originals.eq(index).width())
    });

                        console.log(tr.index());
    return $helper;
},
    updateIndex = function(e, ui) {
        $('td.index', ui.item.parent()).each(function (i) {
            $(this).html(i + 1);
        });

    };

        $("#smartpack-lists tbody").sortable({
            helper: fixHelperModified,
            stop: updateIndex
        }).disableSelection();
});


var saveNewItem = function(elem){
    var listId = $(elem).closest('#new-item-template').find('input[name=list-id]').val();
    smartpackAdmin.saveNewListItem(listId)
};


var toggle = function(elem){
    var listId = $(elem).closest('form').find('.listId').val();
    listId  = listId ? listId : '';
    var switchInput = $('.lists-container').find('input[name=list-id][value="' + listId + '"]').closest('.container-fluid').find('.switch-input');

    //smartpackAdmin.changeStatus(switchInput);
    smartpackAdmin.changeState(switchInput);
};

var rollback = function(obj){
    var parentRow = $(obj).parents('.list-box').eq(0);
    var listId = $(obj).closest('form').find('.listId').val();
    listId  = listId ? listId : '';
    var switchInput = $('.lists-container').find('input[name=list-id][value="' + listId + '"]').closest('.container-fluid').find('.switch-input');
    if(!switchInput[0].checked){
        parentRow.find('[name=status]').val('off');
        parentRow.addClass('disabled');
        $(switchInput).attr('checked',false);
    }
    else{
        parentRow.find('[name=status]').val('on');
        parentRow.removeClass('disabled');
        $(switchInput).attr('checked',true);
    }
};

var showLoading = function() {
    $('.loading').modal('toggle');
}
var hideLoading = function() {
    setTimeout(function() {
        $('.loading').modal('toggle');
    }, 2000);
}

var uploaderOnLoad = function(listId) {
    var obj = $('#lists-id-' + listId);

    if($('#uploader_frame').contents().find('body').text() == "") return false;
    else
    {
        var response = JSON.parse($('#uploader_frame').contents().text());
        var recordsNumber = response.records;
        var messages = response.messages;

        var body = $('#uploader_frame').contents().find('body');
        body.html('');

        for (var i in messages){
            var msg = messages[i];

            if(msg.notice)
                $('<p></p>').text(msg['notice']).appendTo(body);
            if(msg.error)
                $('<p></p>').css({'color' : 'red'}).text(msg['error']).appendTo(body);

        }

        hideLoading();
        smartpackAdmin.initializeIframe(listId);
        smartpackAdmin.setRecords(listId, recordsNumber);

        // TODO
        smartpackAdmin.setDirty(listId);
    }
}
var hideLogger = function() {
    $('#uploader_frame').slideUp();
}

var listEditorResize= function(list_id) {
    var h = $('#list-contents-'+ list_id).find('.list-editor').contents().find('.table-wrapper').css('height');
    $('#list-contents-'+ list_id).find('.list-editor').css('height', h);
}

var notify = function(type,msg) {
    $('#notifier').removeClass()
         .addClass(type)
         .html(msg)
         .fadeIn("fast",function() {
            setTimeout(hide_errors,5000)
          });
}

var hide_errors = function() {
    $('#notifier').fadeOut('slow');
}

$.fn.editable.defaults.mode = 'inline';

var lists_data = <?php echo json_encode($lists); ?>;
var product_types = <?php echo json_encode($productTypes); ?>;
var list_types = <?php echo json_encode($listTypes); ?>;
smartpackAdmin.init(lists_data, list_types, product_types);
</script>
