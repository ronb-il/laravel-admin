<script type="text/javascript" src="scripts/catalog.js"></script>


<input type="hidden" id="_token" value='{{ csrf_token() }}' />

  <div class="row">
        <div class="col-md-12">
            <div class="input-group">
      <input id="search-sku" type="text" class="form-control" placeholder="Search for SKU ...">
      <span class="input-group-btn">
        <button id="goBtn" class="btn btn-primary" type="button" onclick="catalogAdmin.searchItemFromCatalog()">Go!</button>
      </span>
             <div class="btn-toolbar pull-right">
             <button id="importDialog" class="btn btn-primary" type="button" onclick="catalogAdmin.showImportModal()">Import</button>
             <button id="add_row" class="btn btn-primary" type="button" onclick="catalogAdmin.showAddNewItemModal()">Add Item</button>
      </div>
    </div>

<table id="mainTable" class="table table-striped">
<thead>
                  <th>SKU</th>
                  <th>Product ID</th>
                   <th>Product Title</th>
                   <th>Original Price</th>
                   <th>Image Url</th>
                   <th>Product Link</th>
                   <th>Delete</th>
</thead>
<tbody>
</tbody>
<tfoot>
</tfoot>
</table>

        </div>
  </div>
</div>

<div class="modal fade" id="addSkuModal" tabindex="-1" role="dialog" aria-labelledby="addModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Product to catalog</h4>
            </div>
            <div class="modal-body">
                <div class='new-item-form-wrapper simplegrid-full' id='new-item-template'>
       
       <form id="add-item-form" data-toggle="validator" role="form" method="POST" class='new-item-form' action="/catalog/add-item">

      <div class="form-group has-feedback">
         <input name="sku" class="sku form-control" type="text" id="sku" placeholder="SKU" required>
        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        <div class="help-block with-errors"></div>
      </div>

      <div class="form-group has-feedback">
         <input name="product-id" class="sku form-control" type="text" id="product-id" placeholder="Product Id" required>
        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        <div class="help-block with-errors"></div>
      </div>

      <div class="form-group has-feedback">
         <input name="product-title" class="product-title form-control" type="text" id="product-title" placeholder="Product Title" required>
        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        <div class="help-block with-errors"></div>
      </div>

      <div class="form-group has-feedback">
         <input name="original-price" class="original-price form-control" type="text" id="original-price" placeholder="Original Price" required>
        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        <div class="help-block with-errors"></div>
      </div>

      <div class="form-group has-feedback">
         <input name="image-url" class="image-url form-control" type="text" id="image-url" placeholder="Image Url" required>
        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        <div class="help-block with-errors"></div>
      </div>

      <div class="form-group has-feedback">
         <input name="product-link" class="product-link form-control" type="text" id="product-link" placeholder="Product Link" required>
        <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
        <div class="help-block with-errors"></div>
      </div>
        <input type="hidden" name="_token" value='{{ csrf_token() }}' />
        
        </div>
      </div>
          <div class="modal-footer ">
        <button id="addItemBtn" type="submit" class="btn btn-primary btn-lg" style="width: 100%;"><span class="glyphicon glyphicon-ok-sign"></span>Add item to catalog</button>
      </div>
        </div>
  </div>
    </div>
    
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="edit" aria-hidden="true">
      <div class="modal-dialog">
    <div class="modal-content">
          <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
        <h4 class="modal-title custom_align" id="Heading">Delete this entry</h4>
      </div>
          <div class="modal-body">
       
       <div class="alert alert-danger"><span class="glyphicon glyphicon-warning-sign"></span> Are you sure you want to delete this Record?</div>
       
      </div>
        <div class="modal-footer ">
        <button type="button" onclick="catalogAdmin.deleteItemFromCatalog()" id="btnDelteYes" class="btn btn-success" ><span class="glyphicon glyphicon-ok-sign"></span> Yes</button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> No</button>
      </div>
        </div>
  </div>
</div>


    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Upload File</h4>
        </div>
        <div class="modal-body">
        <div class='upload-form-dialog' id='upload-file-template'>
            <form id="import-form" enctype="multipart/form-data" action="/catalog/upload" method="POST" target='uploader_frame'>
                <div class='uploader-wrapper'>
                    <input id="uploadedfile" name="uploadedfile" type="file" class='list-btn' onchange="catalogAdmin.importFile()" />
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
                <input id="_token" type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
            <div class="modal-footer">
                <button data-dismiss="modal" class='btn btn-default pull-right'>Close</button>
            </div>
        </div>
        </div>
    </div>
    </div>
</div>

<!-- Modal -->
<div id="import-catalog-results-form" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Import Summary :</h4>
      </div>
    <div class="modal-body">
        <textarea name="import-results-textarea" id="import-results-textarea" class="form-control" rows="5"></textarea>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>


<div id="notifier"></div>