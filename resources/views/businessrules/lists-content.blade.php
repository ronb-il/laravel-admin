<div class='row'>
    <div class="col-md-5 col-sm-10 hidden-xs">
        <div class="row form-inline">
            <label class="control-label" for="filter-product-type"><b>Filter By Product Type:</b>
            </label>
            <select data-width="fit" id="filter-product-type" class="product_type_filter form-control" onchange="businessAdmin.filterByProductType(this)">
                <option value='all'>All</option>
                @foreach ($productTypes as $key => $name)
                    <option value='{{ $key }}'>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="pull-right form-inline">
        <div class="btn-group" id="add-new-list">
            <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Add New List&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-down"></i></button>
            <ul class="dropdown-menu dropdown-menu-right">
                @foreach ($listTypes as $type)
                    <li><a href="#">{{ $type["description"] }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="input-group custom-search-form search-all">
            <input id="search-all" type="text" class="form-control" placeholder="Search for...">
            <span class="input-group-btn">
                <button onclick="businessAdmin.searchAll();" class="btn btn-default" type="button">
                    <i class="fa fa-search"></i>
                </button>
            </span>
        </div>
    </div>
</div>
<div class="row">
    <table id="business-rules" class="table" width="100%" style="margin-top:20px">
        <tr>
            <th style="vertical-align:middle" width="29%">List Name</th>
            <th style="vertical-align:middle" width="7%">Type</th>
            <th style="vertical-align:middle" width="9%"># Rows</th>
            <th style="vertical-align:middle">Published</th>
            <th style="vertical-align:middle">Last Updated</th>
            <th style="vertical-align:middle" width="13%">Product Type</th>
            <th style="vertical-align:middle" width="4%"><button onclick="businessAdmin.collapseAll()" class="btn btn-primary">Collapse All</button></th>
        </tr>
    </table>
</div>

<div class="modal fade loading" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style='z-index:2000;'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="color:black;">
                <h3 style="margin:0;">Loading</h3>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active" style="margin-bottom:0;">
                <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addNewModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class='new-item-form-wrapper simplegrid-full' id='new-item-template'>
                    <form onsubmit='return false;' method="POST" class='new-item-form'>
                        <p>
                            Fill in the form to add new record to list
                        </p>
                        <div class='inputs-row'>
                            <div class='margin-top'>
                                <input type='text' name='f1' class='list-type-variable' placeholder=''>
                            </div>
                            @if(!$excluded)
                            <div class='margin-top'>
                                <input type='text' name='f2' placeholder='Discount (%)'>
                            </div class='margin-top'>
                            <div class='margin-top'>
                                <input type='text' name='f3' placeholder='Min Price'>
                            </div>
                            @endif
                            <div class='margin-top'>
                                <input type='text' name='f4' placeholder='MetaData1'>
                            </div>
                            <div class='margin-top'>
                                <input type='text' name='f5' placeholder='MetaData2'>
                            </div>
                        </div>
                        <input type="hidden" name="_token" value='{{ csrf_token() }}' />
                        <input type='hidden' name="list-type" value="">
                        <input type='hidden' name="list-id" value="">
                        <input type="hidden" name="insert" value='true' />
                        <input type='hidden' name="excluded" value="{{ $excluded }}">
                        <div class="modal-footer">
                            <div class="btn-toolbar pull-right">
                                <button onclick="saveNewItem(this)" class='btn btn-primary btn-md'>Add Item</button>
                                <button data-dismiss="modal" class='btn btn-default btn-md'>Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="importModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Upload File</h4>
        </div>
        <div class="modal-body">
        <div class='upload-form-dialog' id='upload-file-template'>
            <form enctype="multipart/form-data" action="/listitems/upload" method="POST" target='uploader_frame'>
                <div class='uploader-wrapper'>
                    <input type="hidden" name="_token" value='{{ csrf_token() }}' />
                    <input name="uploadedfile" type="file" class='list-btn' onchange="businessAdmin.showUploadLogger(this);" />
                </div>
                <div class='instructions'>
                    <div class='instruction'>
                        <span class='star'>*</span> CSV files only.
                    </div>
                    <div class='instruction'>
                        <span class='star'>*</span> Max file size 40MB.
                    </div>
                    <div class='instruction'>
                        <span class='star'>*</span> File <b>must</b> contain headers
                    </div>
                </div>
                <div class='uploader-container'>
                </div>
                <div class='bold important margin-top note'>Note: The imported file will replace the content of the list.</div>
                <br/>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="list-id" value='-1' />
                <input type="hidden" name="list-type" value='' />
                <input type="hidden" name="excluded" value='' />
            </form>
            <div class="modal-footer">
                <button data-dismiss="modal" class='btn btn-default pull-right'>Close</button>
            </div>
        </div>
        </div>
    </div>
    </div>
</div>

<div id="notifier"></div>
