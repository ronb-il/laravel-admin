@extends('layouts.admin')

@section('sidebar-content')
  @include('variations/sidebar')
@endsection

@section('content')
    <style>
        a {cursor:pointer;}
        .lab-variant-admin-selector-row {margin-top:30px;height:40px;clear:both;}

        #lab-variant-admin-wrapper {margin-bottom:20px;min-height:450px;}
        #lab-variant-admin-wrapper .variation-admin-row {height:250px;margin-top:15px;padding-top:10px;background-color:#fff;}
        #lab-variant-admin-wrapper .header-name {height:50px;text-transform: capitalize;}
        #lab-variant-admin-wrapper .variation-admin-cell {}
        #lab-variant-admin-wrapper .buttons {text-align: right;padding-top: 25px;padding-right:25px;}
        #lab-variant-admin-wrapper .variation-admin-cell.name h5 {color:#666;}
        #lab-variant-admin-wrapper .variation-admin-cell textarea , #lab-variant-admin-wrapper .variation-admin-cell input{width:100%;}
        #lab-variant-admin-wrapper .variation-admin-cell .input-holder {width:85%;margin:0 auto;}
        #lab-variant-admin-wrapper .variation-admin-cell div.conflicts {height:75%;border:solid 1px #e1e1e1;width:100%;padding-left:10px;}
        #lab-variant-admin-wrapper .variation-admin-cell input {border:solid 1px #e1e1e1;padding:3px 10px;}
        #lab-variant-admin-wrapper .variation-admin-cell label {display:block;font-weight:bold;margin-bottom:20px;margin-left:7.5%;font-size:15px;}
        #lab-variant-admin-wrapper .variation-admin-cell .conflict-helper {float:right;width:200px;margin-right:15px;margin-top:-40px;}
        #lab-variant-admin-wrapper .notice {width:100%;}
        #lab-variant-admin-wrapper .notice .important {color:white;width:400px;margin:0 auto;background-color: red;font-weight:bold;border-radius:5px;padding:10px;}

        .textarea-json { height:320px; width:100%; }
    </style>

    <div id="lab-variant-admin-wrapper">
        <div class='notice'>
            @if(count($activeSets) > 0)
                <div class='important'>
                    Pay Attention: This UX element is disabled and still alive in:
                    </br>
                    @foreach($activeSets as $row)
                        {{$row['affiliate_name']}} : {{$row['set_name']}}
                        <br/>
                    @endforeach
                </div>
            @endif
        </div>

        <form class="form-horizontal" role="form" method="POST">
          <input type='hidden' name='id' value="{{ $variationConfig['id'] }}" />
          <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}" >
          <div class="form-group">
            <label class="control-label col-sm-2" for="variation-id">Variant:</label>
            <div class="col-sm-10">
            <select name="variation-id" id="variant-selector" class='form-control variant-selector' style="width:auto;">
                <option value='-1'>Select Variant</option>
                @foreach($variantOptions as $variantOption)
                <option value="{{ $variantOption['id'] }}" {{ ($id == $variantOption['id']) ? 'selected' : '' }}>{{ $variantOption['name'] }}</option>
                @endforeach
            </select>
            </div>
          </div>

          @if(!empty($variationConfig['id']))
          <div class="form-group">
            <label class="control-label col-sm-2" for="conflicts">Conflicts:</label>
            <div class="col-sm-10">
              <select name="conflicts[]" id="conflicts" class="form-control select2" multiple="multiple" style="width:auto;">
                @foreach($variantOptions as $variantOption)
                    <option value="{{ strtolower($variantOption['name']) }}" {{ in_array(strtolower($variantOption['name']), array_map('strtolower', $variationConfig['conflicts'])) ? 'selected' : '' }}>{{ $variantOption['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2">Status:</label>
            <div class="col-sm-10">
              <label for="status_enabled"><input type="radio" name="status" id="status_enabled" value="1" {{ ($variationConfig['status'] == "1") ? 'checked' : '' }}> Enabled</label><br>
              <label for="status_disabled"><input type="radio" name="status" id="status_disabled" value="0" {{ ($variationConfig['status'] == "0") ? 'checked' : '' }}> Disabled</label><br>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2">Device Compatibility:</label>
            <div class="col-sm-10">
              <div id="devices" class="selectable-image-selector" style="padding-top:10px;">
                <ul class="device-types clearfix">
                  <li>
                    <input id="desktop" type="checkbox" name="device-type" value="desktop" />
                    <label class="selectable-image device desktop" for="desktop"></label>
                  </li>
                  <li>
                    <input id="mobile" type="checkbox" name="device-type" value="mobile" />
                    <label class="selectable-image device mobile"for="mobile"></label>
                  </li>
                  <li>
                    <input id="tablet" type="checkbox" name="device-type" value="tablet" />
                    <label class="selectable-image device tablet"for="tablet"></label>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="description">Content:</label>
            <div class="col-sm-10">
              <textarea name="description" id="description" class="desc">{{ $variationConfig['description'] }}</textarea>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="json">Json:</label>
            <div class="col-sm-10">
              <textarea name="json" id="json" class="textarea-json">{{ $variationConfig['json'] }}</textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button type="submit" class="btn btn-default">Save</button>
            </div>
          </div>
          @endif
        </form>
    </div>
@endsection

@section('custom-javascript')
    <script src="{{ url('scripts/variations.js') }}"></script>
    <script src="{{ url('tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript">
      $(document).ready(function () {
          //Check if string is Json
          function IsJsonString(str) {
              try {
                  JSON.parse(str);
              } catch (e) {
                  return false;
              }
              return true;
          }

          function updateDeviceTypes() {
            var jsonConfigString = $(".textarea-json").val();
            if (IsJsonString(jsonConfigString)) {
              var jsonObj = JSON.parse(jsonConfigString);
              for(device in jsonObj.devices) {
                // set it to checked
                $("#devices input[name='device-type'][value='" + jsonObj.devices[device] + "']").attr('checked','checked');
              }
            }
          }

          //Parsing the textarea to display in JSON format
          var textareas = $(".textarea-json");

          // bind onClick of device type
          $("input[name='device-type']").on('click', function(e){
            var jsonConfigString = $(".textarea-json").val();

            if(IsJsonString(jsonConfigString)){
               var jsonObj = JSON.parse(jsonConfigString);

               jsonObj.devices = jsonObj.devices || [];

               var indexOfDevice = jsonObj.devices.indexOf(e.target.value);

               if (e.target.checked && (indexOfDevice === -1)) {
                jsonObj.devices.push(e.target.value);
               } else if (indexOfDevice > -1) {
                jsonObj.devices.splice(indexOfDevice,1);
                // delete jsonObj.devices[e.target.value];
               }

               var jsonPretty = JSON.stringify(jsonObj, null, '\t');
               document.getElementById('json').value = jsonPretty;
            }
          })

          $.each(textareas, function(index, textAreaObject) {
              if(IsJsonString(textAreaObject.value)) {
                  var jsonObj = JSON.parse(textAreaObject.value);
                  var jsonPretty = JSON.stringify(jsonObj, null, '\t');
                  textAreaObject.value = jsonPretty;
              } else {
                  if(textAreaObject.value != '') {
                      textAreaObject.style.background = '#E34D4D';
                  }
              }
          });

          $(".textarea-json").keyup(function(){
              if(!IsJsonString(this.value) && this.value != '') {
                  this.style.background = '#E34D4D';
              } else {
                  this.style.background = '#fff';
              }
          })

          tinymce.init({
              height:'420',
              selector:'textarea.desc',
              plugins: "textcolor colorpicker link code",
              toolbar: "forecolor backcolor | styleselect | bold italic | link image | undo redo | alignleft aligncenter alignright code",
              extended_valid_elements : "iframe[src|frameborder|style|scrolling|class|width|height|name|align]"
          });

          $('#variant-selector').change(function() {
              var id = $( this ).val();
              if (id > 0) {
                  window.location.href = '{{ url('variations/admin') }}' + '/' +  id
              }
          });

          // show default selected options
          updateDeviceTypes();
      });

    </script>
@endsection
