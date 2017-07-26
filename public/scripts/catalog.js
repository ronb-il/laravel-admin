 $(document).ready(function() {
     $('#addSkuModal').on('hidden.bs.modal', function() {
         $(this).find('.sku , .product-title, .original-price ,.image-url , .product-link , .product-id').val('').end();
          // $('#add-item-form').validator('destroy');
          $('#add-item-form')[0].reset();
     });

     $('#add-item-form').validator().on('submit', function(e) {
         if (e.isDefaultPrevented()) {

         } else {
             catalogAdmin.addNewItemToCatalog();
             return false;
         }
     });

 $("#search-sku").keyup(function(event) {
 if (event.keyCode == 13) {
     $("#goBtn").click();
 }
 });
 });



 var catalogAdmin = (function() {
     var public = {},
         privateVariable = 1;

     public.addNewItemToCatalog = function() {
         // var addSkuModal = $('#addSkuModal');

                      //   var sku = $('.sku', addSkuModal).text();
             $('#addSkuModal').modal('hide');

             $.post("catalog/add-item", $('#add-item-form').serialize(), function(response) {
                 if (response.status == "true") {
                     notify("success", response.message);
                 } else if (response.status == "error") {
                     notify("error", response.message);
                 }
             });
     }

     function addDataToTable(item) {
         var rowHtml = ' \
            <tr>\
            <td width="17%" class="sku"><span name="sku">' + item.sku + '</span></td>\
            <td width="17%" class="product-id"><span data-pk="sku" data-name="product-id">' + item.productId + '</span></td>\
            <td width="17%" class="product-title"><span data-pk="sku" data-name="product-title">' + item.title + '</span></td>\
            <td width="17%" class="original-price"><span data-pk="sku" data-name="original-price">' + item.originalPrice + '</span></td>\
            <td width="17%" class="image-url"><span data-pk="sku" data-name="image-url">' + item.imageUrl + '</span></td>\
            <td width="17%" class="product-link"><span data-pk="sku" data-name="product-link">' + item.referer + '</span></td>\
            <td width="17%"> <p data-placement="top" data-toggle="tooltip" title="Delete"><button class="btn btn-danger btn-xs delete" data-title="Delete" data-toggle="modal" onclick="catalogAdmin.deleteItemFromCatalog()" ><span class="glyphicon glyphicon-trash"></span></button></p></td>\
            </tr>';
         return rowHtml;
     }

     public.searchItemFromCatalog = function() {

         var tb = document.getElementById('mainTable');
         while (tb.rows.length > 1) {
             tb.deleteRow(1);
         }

         $.ajax({
             url: "/catalog/search-item",
             type: "POST",
             data: {
                 _token: $('#_token').val(),
                 sku: $("#search-sku").val()
             },
             success: function(response) {
                 if (response.status == "true") {
                     var item = response.item;
                     var rowHtml = addDataToTable(item)
                     $('table tr:last').after(rowHtml);

                     $('.product-id span , .product-title span, .original-price span ,.image-url span, .product-link span').editable({
                         type: 'text',
                         mode: 'inline',
                         params: {
                             _token: $('#_token').val(),
                             sku: $(".sku").text()
                         },
                         url: '/catalog/save'
                     });
                 } else if (response.status == "error") {
                     notify("error", response.message);
                 }
             },
             error: function() {}
         });
     };

     public.deleteItemFromCatalog = function() {

    if (confirm("Are you sure you want to delete this item ?")) {
         $('table').on('click', '.delete', function() {
             $(this).parents('tr').remove();
         });
         $.ajax({
             url: "/catalog/delete-item",
             type: "POST",
             data: {
                 _token: $('#_token').val(),
                 sku: $(".sku").text()
             },
             success: function(response) {
                 if (response.status == "true") {
                     notify("success", response.message);
                 } else if (response.status == "false") {
                     notify("error", response.message);
                 }
             },
             error: function() {
                notify("error", "connection error");
             }
         });
     };
 };

     public.showAddNewItemModal = function() {
         $('#addSkuModal').modal('show');
     };

     public.showImportModal = function() {
         $('#importModal').modal('show');
     };

    public.importFile = function() {
        
        
        var formData = new FormData($('#importModal'));
        formData.append('_token', $('#_token').val());
        formData.append('uploadedfile', $("#uploadedfile")[0].files[0]);

        $.ajax({
            url: "/catalog/upload",
            type: 'POST',
            data: formData,
            cache: false,
            dataType: "json",
            contentType: false,
            processData: false,
         
            success: function(data) {
                setResultToResultsModalAndShow(data);
             },
             error: function(data) {
                setResultToResultsModalAndShow(data);
             }

        });

        $('#importModal').modal('hide');
        $("#uploadedfile").val("");
     };

    function setResultToResultsModalAndShow(data) {
        // $(".modal-body #import-results-textarea").val("Total records : " + data['allRecordsCounters'] + 
        //     "\nTotal inserted records : " + data['insertedRecordsCounters']);
        $(".modal-body #import-results-textarea").val("not final dialog ,  dynamo is still in progress - 20k should take ~5 minutes");

        $('#import-catalog-results-form').modal('show');
        return true;
     };



     return public;
 }());