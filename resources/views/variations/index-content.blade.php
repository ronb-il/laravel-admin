    <?php
    $dynamic_styles = "";
    $colors_array = array(
            "#be5333","#3f97ad","#ac2f2b",
            "#742343","#585313","#74aa48",
            "#24584b","#c23e6f","#194e70",
            "#cd7a82","#c4be8c","#75abb7",
            "#583d2c","#c4c4c2","#5b6f70",
            "#e07330","#c0b3a3","#8fcdbe",
            "#fed130","#e55554","#228a97",
            "#aec446","#f5ae30","#950f4c",
            "#edea43","#54cbed","#f1c5de",
            "#92a5a9","#cf9099","#faaa6b",
            "#00b36f","#b4e3ed","#98826b",
            "#6c8a28","#518d85","#006ab4",
            "#d36dac","#aea1aa"
    );
    shuffle($colors_array);
    $index = 0;
    foreach($variationsConfig as $config)
    {
        $variation_name = strtolower(str_replace(" ","_",$config["name"]));
        $json = json_decode($config["json"],true);
        $fields = $json["fields"];
        if(isset($fields)) {
            foreach($fields as $field)
            {
                $name = strtolower(str_replace(" ","_",$field["name"]));
                $color = $colors_array[$index];
                $dynamic_styles.= "
                .variation-preview-row-content.{$variation_name} .graph-item.{$name} {
                    background-color:$color;
                    color:white;
                }
                .variation-preview-row-content.{$variation_name} .graph-item.{$name}:hover {
                    color:black;
                    font-weight:bold;
                }
            ";
                $index++;
                if(count($colors_array) == $index) $index = 0;
            }
        }
    }
    echo "
    <style>
        $dynamic_styles
    </style>
"
    ?>
    <div class="row">
        <button class="btn btn-primary float-right" onclick='Personali.lab.addSet()'><span>New Configuration Set</span></button>
    </div>
    <div class="row variations-content">
        <div class="variations-slider">
            <div class="variations-sets-container slide">
            </div>
        </div>
    </div>


    <!-- STARTING WITH TEMPLATES -->
    <div class="variations-templates" style='display:none'>
        <!-- VARIATION SET AKA EXPIRIENCE -->
        <div class="variations-set container_12" id='variation-set-template' onclick='Personali.lab.selectSet(this);'>
            <div class="configuration-set-head">
                <div class="row">
                    <input type="hidden" class="variation-json" value="" />
                    <input type="hidden" class="variation-id" value="-1" />
                    <input type="hidden" class="variation-status" value="enabled" />
                    <div class="cell" style="width:20%">
                        <input type="text" class="variation-name bold" placeholder="Variation Name" />
                    </div>
                    <div class="cell" style="width:30%">
                        <input type="text" class="variation-desc" placeholder="Description" />
                    </div>
                    <div class="cell" style="width:30%">
                        <div class="variation-buttons-container">
                            <button name="delete" class="btn btn-default" onclick="Personali.lab.removeSet(this)">Delete</button>
                            <button name="json" class="btn btn-default" onclick="Personali.lab.preview(this)">Json</button>
                            <button name="save" class="btn btn-default" onclick="Personali.lab.save(this)">Save</button>
                            <button name="save-publish" class="btn btn-default" onclick="Personali.lab.save(this,true)">Save &amp; Publish</button>
                        </div>
                    </div>
                    <div class="cell" style="width:15%">
                        <div class="actions-selector-container">
                            <select class='actions-options' placeholder="Add UX Element" onchange='Personali.lab.addAction(this);' >
                                <option value="-1">Add UX Element</option>
                            </select>
                        </div>
                    </div>
                    <div class="cell" style="width:5%">
                        <a name="toggle-set-content" class="btn variation-button white close" onclick='Personali.lab.toggle(this)'>+</a>
                    </div>
                </div>
            </div>
            <div class="variations-set-content clear-both relative">
                <div class="container_12 actions-container"></div>
            </div>
        </div>

        <!-- VARIATION CONTENT BOX  == ACTION BOX  -->
        <div class="variation-content-box" id='variation-content-template'>
            <div class="eighth">
                <h5 class='action-name'></h5>
                <div onclick="Personali.lab.showInfo(this)" class='small-tool-btn'>?</div>
            </div>
            <div class="three-quarters graph-preview">
                <form class="variations-content-form" method=post>&nbsp;</form>
            </div>
            <div class="eighth variation-content-box-toolbar" style="vertical-align: middle;">
                <a href="#" onclick="Personali.lab.removeVariationBox(this);return false;"><i class="fa fa-trash-o fa-fw"></i></a>
                <a href="#" onclick="Personali.lab.editOptions(this);return false" class='edit-option'><i class="fa fa-pencil-square-o fa-fw" style="vertical-align: middle;"></i></a>
            </div>
        </div>
        <!---->
        <div class="variation-form-input-wrapper" id="variation-form-input-box-wrapper-template"></div>
        <!---->
        <div class="variation-preview-row" id="variation-preview-row-template"></div>
        <!---->
        <div class='variation-form-input-box' id="variation-form-input-box-template">
            <div>
                <label class="variation-box-name"></label>
            </div>
            <div class="three-quarters">
                <div class="input-holder"></div>
            </div>
            <div class="quarter">
            <span class="variation-form-input-box-toolbar">
                <a href="#" onclick="Personali.lab.deleteVariation(this);return false;"><i class="fa fa-trash-o fa-fw"></i></a>
            </span>
            </div>
        </div>

        <div class='variation-preview-row-content simplegrid-full' id="variation-preview-row-content-template">
            <div class="eighth left-title">
                <label class="preview-variant-name" align=left onclick="Personali.lab.editOptions(this);"></label>
            </div>
            <div class="seven-eighth right-content relative">
                <div class="variants">
                </div>
            </div>
        </div>

        <div class='graph-item' id="graph-item-template">
            <span class='item-value'></span>
        </div>

    </div>

    <div id="variations-notifier"></div>

    <div id="variants-modal" class="modal fade" role="dialog" aria-hidden="true">
        <form class='editor-form' onsubmit="return false" method=post>
            <div class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title variant-name">Modal Header</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class='editor-headline' align=left>Settings</h6>
                            </div>
                            <div class="col-md-8">
                                <h6 class='editor-headline' align=left>Variation <span class='variant-name-title'></span></h6>
                            </div>
                        </div>
                        <div class="row variants-editor-container">
                            <div class="col-md-4">
                                <div class="list-group variants-list"></div>
                            </div>
                            <div class="col-md-8 variants-list-options">
                                <div class='options-selector simplegrid-full'></div>
                                <div class='simplegrid-full add-variation-wrapper'>
                                    <a class='variation-btn white btn' onclick="Personali.lab.cloneVariation()">+ Add Variation</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button data-dismiss="modal" class='btn btn-default'>Close</button>
                        <button data-dismiss="modal" class='btn btn-default save-variants-editor' onclick="Personali.lab.saveEditor()">Update</button>
                    </div>
                </div>
            </div>
            <input type="hidden" value="-1" class="editable-form-id" />
        </form>
    </div>


    <div class="simple-modal">
        <h5 class="modal-title" align=center></h5>
        <div class="modal-content"></div>
        <div class="modal-close"><a onclick="simpleModal.close();" class='variation-btn white'>Close</a></div>
    </div>

    <div class="simple-modal-overlay"></div>
