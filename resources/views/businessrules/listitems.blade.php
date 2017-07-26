<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="{{ url('styles/retailer-all.css') }}" rel="stylesheet">
    <link href="{{ url('styles/retailer-lists.css') }}" media="screen" rel="stylesheet" type="text/css" />
    <link href="{{ url('styles/frontend.css') }}" rel="stylesheet">
    <link href="{{ url('styles/datatables.css') }}" rel="stylesheet">
    <link href="{{ url('styles/dataTables.bootstrap.css') }}" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    <style type="text/css">
        body {font-family: 'Lato'; width:100%;border:none;background-color:white;}
        a { cursor:pointer; }
        a:hover {text-decoration:none;}
        .table-wrapper {margin-top:10px;margin-left: auto;margin-right:auto;padding:10px;}
        #item-edit-form {display:none;}
    </style>
</head>
<body>

<div class="box round grid table-wrapper">
    <table class="data display datatable" id="list-items-table" width=100% align=center>
        <thead>
            <tr>
            @foreach($headers as $key => $header)
                <th id="{{ $key }}" style='background-color:#8F8F8F;color:white'>{{ $header }}</th>
            @endforeach
                <th style='background-color:#8F8F8F;color:white'>Actions</th>
            </tr>
        </thead>
    </table>
</div>

<form id='item-edit-form'>
    <input type='hidden' name='_token' class='dont-clear' value='{{ csrf_token() }}' />
    <input type='hidden' name='list-id' class='dont-clear' value='{{ $list_id }}' />
    <input type='hidden' name='list-type' class='dont-clear' value='{{ $list_type }}' />
    <input type='hidden' name='excluded' class='dont-clear' value='{{ $excluded }}' />
</form>

<script src="{{ url('scripts/frontend.js') }}"></script>
<script src="{{ url('scripts/jquery.url.js') }}"></script>
<script src="{{ url('scripts/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ url('scripts/dataTables.bootstrap.js') }}" type="text/javascript"></script>
<script src="{{ url('scripts/businessrules.js') }}" type="text/javascript"></script>


<script type="text/javascript">
 var list_id = '{{ $list_id }}';
 var ext_id = '{{ $ext_id }}';
 var list_type = '{{ $list_type }}';

 $(document).ready(function(){
    var dt = $('#list-items-table');

    dt.dataTable( {
        ajax: {
            url: '/listitems/json?list_id=' + list_id,
        },
        "processing": true,
        "serverSide": true,
        columns: [
            { data: "f1" },
            { data: "f2" },
            { data: "f3" },
            { data: "f4" },
            { data: "f5" },
            { data: "serial_id",
                render: function(data) {
                    return "<span class='actions'><a _id='"+data+"' class='edit hide_edit_mode margin-right'><span class='icon-edit'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></a><a _id='"+data+"' class='delete hide_edit_mode margin-right'><span class='icon-close-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></a></span>";
                     },orderable: false }
        ],
        select: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "fnInitComplete": function(oSettings, json) {
            parent.listEditorResize(list_id);
            $("select[name='list-items-table_length']").bind('change', function(){
                parent.listEditorResize(list_id);
            });
        }
    } );

    @if($excluded == "1")
    dt.fnSetColumnVis(1, false);
    dt.fnSetColumnVis(2, false);
    @endif

    var searchString = $.url().data.param.query['search_q'];
    // Replacing url encode from + to empty space for the search query
    searchString = searchString.replace(new RegExp("\\+","g"),' ');
    if(searchString) {
        dt.fnFilter(searchString);
    }


    $('#list-items-table').on('click', '.edit', function() {
        var item_id = $(this).attr('_id');
        var tr = $(this).parents('tr').eq(0);

        var headings = $("#list-items-table thead th");

        tr.find('.hide_edit_mode').hide();

        tr.find('td').each(function(index, el) {
            // check if it's not span with action class
            if ($(el).find('span.actions').length) {
                $(this).append("<input type=hidden value='"+item_id+"' name='item_id' />");
                $(this).append("<button class='save form-control input-sm show_edit_mode'>save</button> <button class='cancel form-control input-sm show_edit_mode'>cancel</button>");
            } else {
                var default_txt = $(this).text();
                $(this).html("<span style='display:none' class='bk'>"+default_txt+"</span><input type=text class='form-control input-sm' value='"+default_txt+"' name='" + headings.eq(index).attr('id') + "' />");
            }
        })
    })

    $('#list-items-table').on('click', '.save', function() {
        $('#item-edit-form input:not(.dont-clear)').remove();

        var tr = $(this).parents('tr').eq(0);
        tr.find('input').clone().appendTo('#item-edit-form');
        var data = $('#item-edit-form').serialize();

        var err = function() {
            cancelEdit(tr);
        }
        var ok = function() {
            okEdit(tr);
        }

        businessAdmin.saveListItem(data, ok, err);
    })

    var cancelEdit = function(tr) {
        tr.find('td').each(function(index, el) {
            if (!$(el).find('span.actions').length) {
                var default_txt = $(this).find('.bk').text();
                $(this).html(default_txt);
            }
        })
        tr.find('.show_edit_mode').remove();
        tr.find('.hide_edit_mode').show();
    }

    var okEdit = function(tr) {
        tr.find('td').each(function(index, el) {
            if (!$(el).find('span.actions').length) {
                var default_txt = $(this).find('input').val();
                $(this).html(default_txt);
            }
        })
        tr.find('.show_edit_mode').remove();
        tr.find('.hide_edit_mode').show();
    }

    $('#list-items-table').on('click', '.cancel', function() {
        var tr = $(this).parents('tr').eq(0);
        cancelEdit(tr);
    })

    $('#list-items-table').on('click', '.delete', function() {
        var tr = $(this).parents('tr').eq(0);
        var item_id = $(this).attr('_id');
        rowIdx = tr.index()
        businessAdmin.deleteListItem(item_id, list_id, function() {
            dt.fnDeleteRow(rowIdx)
        });
    })

});
</script>
</body>
</html>
