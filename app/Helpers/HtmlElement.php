<?php
/**
 * Created by PhpStorm.
 * User: Amir
 * Date: 4/1/2016
 * Time: 4:03 PM
 */

namespace App\Helpers;


class HtmlElement
{
    public static function htmlElement(){
        //return $this;
    }

    public static function fromJSON($elementId, $config){

        $display_name = isset($config["displayName"])?$config["displayName"]:"";
        $description = null;

        switch($config['type']){
            case "openRange":
                return self::openRangeElement($elementId, null, $config['maxLength'], $config['parts'],$display_name);
                break;
            case "range":
                return self::rangeElement($elementId, $config['values'],$description,$display_name);
                break;
            case "freeText":
                return self::freeTextElement($elementId, $description, $config['maxLength'],$display_name);
                break;
            case "freeTextArea":
                return self::freeTextAreaElement($elementId, $description, $config['maxLength'],$display_name);
                break;
        }

        return '';
    }

    private static function rangeElement($elementId, $values=null, $description = null,$display_name = ""){
        $options = '';
        foreach ($values as $option){
            $options .= "<option value='{$option}'>{$option}</option>";
        }
        $element_name = $display_name != ""?$display_name:$elementId;
        return <<<RANGE_ELEMENT
            <script type="text/template" id="template-{$elementId}">
                <div class="{$elementId}-wrapper element-wrapper">
                    <div class="element-id">
                        {$element_name}:
                    </div>

                    <form class="form-inline" role="form">
                        <div class="input-group {$elementId}-wrapper">
                              <span role="button" onclick="javascript:removeElement($('#rule-<%= ruleId %> .{$elementId}-wrapper'))" class="input-group-addon btn btn-default element-x" type="button">X</span>
                               <select id="{$elementId}" class="{$elementId} form-control">
                                   <option disabled="disabled">{$elementId}</option>
                                   {$options}
                               </select>
                        </div>
                    </form>
                </div>
            <script type=type="text/javascript" id="template-{$elementId}-apply-after">
                        $("#rule-<%= ruleId %> .{$elementId}-wrapper #{$elementId} option[value='<%= value %>']").attr("selected", true);
            </script>
        </script>
RANGE_ELEMENT;
    }


    private static function openRangeElement($elementId, $description = null, $maxLength=1000, $parts,$display_name = ""){
        $element_name = $display_name != ""?$display_name:$elementId;
        return <<<OPEN_RANGE_ELEMENT
        <script type="text/template" id="template-{$elementId}">
                <div class="{$elementId}-wrapper element-wrapper">
                    <div class="element-id">
                        {$element_name}:
                    </div>
                    <form class="form-inline" role="form">
                        <div class="input-group">
                              <span role="button" onclick="javascript:removeElement($('#rule-<%= ruleId %> .{$elementId}-wrapper'))" class="input-group-addon btn-default element-x" type="button">X</span>
                              <input type="text" id="{$elementId}"  class="{$elementId} form-control element" placeholder="{$elementId} value..." value="<%= value %>" maxLength="{$maxLength}"></input>
                        </div>
                    </form>

                </div>
                <script type=type="text/javascript" id="template-{$elementId}-apply-after">
                    $("#rule-<%= ruleId %> .{$elementId}-wrapper #{$elementId}").blur(function(){
                            var elementValue =  $(this).val();
                            items = elementValue.split('-');
                            if(items.length != {$parts}){
                                alert("{$elementId} must include {$parts} parts. use '-' as a separator");
                                $(this).focus();
                            }
                    });
                </script>
        </script>
OPEN_RANGE_ELEMENT;
    }

    private static function freeTextElement($elementId, $description = null, $maxLength=1000,$display_name = ""){
        $element_name = $display_name != ""?$display_name:$elementId;
        return <<<FREE_TEXT
        <script type="text/template" id="template-{$elementId}">
                <div class="{$elementId}-wrapper element-wrapper">
                    <div class="element-id">
                        {$element_name}:
                    </div>
                    <form class="form-inline" role="form">
                        <div class="input-group">
                              <span role="button" onclick="javascript:removeElement($('#rule-<%= ruleId %> .{$elementId}-wrapper'))" class="input-group-addon btn-default element-x" type="button">X</span>
                              <input type="text" id="{$elementId}"  class="{$elementId} form-control element" placeholder="{$elementId} value..." value="<%= value %>" maxLength="{$maxLength}"></input>
                        </div>
                    </form>
                </div>
        </script>
FREE_TEXT;
    }

    private static function freeTextAreaElement($elementId, $description = null, $maxLength=1000,$display_name = ""){
        $element_name = $display_name != ""?$display_name:$elementId;
        return <<<FREE_TEXT_AREA
        <script type="text/template" id="template-{$elementId}">
                <div class="{$elementId}-wrapper element-wrapper">
                    <div class="element-id">
                        {$element_name}:
                    </div>
                    <form class="form-inline" role="form">
                        <div class="input-group">
                              <span onclick="javascript:removeElement($('#rule-<%= ruleId %> .{$elementId}-wrapper'))" role="button" class="input-group-addon btn-default element-x" type="button">X</span>
                              <textarea id="{$elementId}"  class="{$elementId} form-control element" placeholder="{$elementId} value..." value="<%= value %>" maxLength="{$maxLength}"><%= value %></textarea>
                        </div>
                    </form>
                </div>
                <script type=type="text/javascript" id="template-{$elementId}-apply-after">
                        $("#rule-<%= ruleId %> .{$elementId}-wrapper #{$elementId}").elastic();
                        $("#rule-<%= ruleId %> .{$elementId}-wrapper #{$elementId}").blur(function(){
                            if($(this).val().length >= {$maxLength}){
                                alert('Value has exceeded the limit of ' + {$maxLength});
                                $(this).foucs();
                            }
                        });
                </script>
        </script>
FREE_TEXT_AREA;
    }
}
