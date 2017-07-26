@extends('layouts.admin')

@section('sidebar-content')
    @foreach($reports as $key => $report)
        <!--08-09-16 : This Ab report will be deprecated soon-->
        @if($report['title'] == 'Metrics Legend')
            <li><a href="{{ url("/abreport") }}?{{ Request::getQueryString() }}">AB Report</a></li>
        @endif
        <li><a href="{{ url("/reports/$key") }}?{{ Request::getQueryString() }}">{{ $report['title'] }}</a></li>
    @endforeach
        
@endsection

@section('content')

<?php
if(count($data) == 0){
  echo <<<ERROR
<div class="container-fluid">
    <div class='row'>
        <div class='col-md-8 col-sm-10'>
            <div class="panel panel-default"> 
                <div class="panel-body">Please choose a customer from the cutomers drop down
                </div> 
            </div>
        </div>
    </div>
</div>
ERROR;
}?>

<script type="text/javascript">
  window.addEventListener('sessionchanged', function (e) {
    var affiliateIds = $('#inputAffiliateId').val().split(",");
    location.reload();
  });
</script>

<style>
    /* Admin - AB Funnel START */
  
  #divFunnel .nfunnel{
    font: 13px/1.5 Tahoma, Helvetica, Arial, 'Liberation Sans', FreeSans, sans-serif;
    padding:5px;
    box-sizing: border-box;
    margin-left: auto;
      margin-right: auto;
  }

  #divFunnel .nfunnel .advanced{
    display:none;
  }

  #divFunnel .nfunnel .left-Row{
    float:left;
    width:190px;
    height:100%;
    border-left: solid 2px black;
      border-top: solid 2px black;
      border-bottom: solid 2px black;
  }

  #divFunnel .nfunnel .left-Row .top-left-corner{
    outline: solid 4px #181E2E;
    height: 107px;
    margin: 11px;
  }

  #divFunnel .nfunnel .left-Row .top-left-corner .test-group{
    text-align:center;
    font-size: 17px;
    font-weight: bold;
    height: 56px;
  }
  #divFunnel .nfunnel .left-Row .top-left-corner img{
      margin-left: 16px;
  }

  #divFunnel .nfunnel .left-Row .left-square{
      margin:7px;
      text-align:center;
      height:100px;
      background-color:#4c4c4c;
      color:white;
      font-size:16px; 
  }

  #divFunnel .nfunnel .left-Row .left-square .subtext{
      font-size: 12px;
      position: relative;
      top: 34px; 
  }

  #divFunnel .nfunnel .left-Row .left-square .text-block{
    position: relative;
    top: 35px;
  }

  #divFunnel .nfunnel .right-content{
    float:left;
    height:100%;
    border-right: solid 2px black;
      border-top: solid 2px black;
      border-bottom: solid 2px black;
  }

  #divFunnel .nfunnel .right-content .top-groups{
    width:100%;
    height:122px;
    margin-bottom:7px;
  }

  #divFunnel .nfunnel .right-content .top-groups .right-square{
      margin:7px 7px 0px 0px;
      text-align:center;
      height:115px;
      background-color:#f2f2f2;
      color:#4c4c4c;
      font-size:15px;
      float:left;
      width: 190px;
      border:solid 1px;
  }

  #divFunnel .nfunnel .right-content .top-groups .right-square.disabled{
    background-color: #4c4c4c;
      color: #FFFFFF;
  }

  #divFunnel .nfunnel .right-content .top-groups .right-square .content{
      position:relative;
      top:10px;
  }

  #divFunnel .nfunnel .right-content .funnelrow{
    height:100px;
    background-color:white;
    margin-bottom:7px;
    margin-left:1px;
  }

  #divFunnel .nfunnel .right-content .cell{
      width: 190px;
      float:left;
      height:100%;
      margin:0px 7px 0px 0px;
      text-align:center;
  }

  #divFunnel .nfunnel .right-content .funnelrow .cell.light-blue{
      outline:solid 1px white;
      background-color:#f2f2f2;
  }

  #divFunnel .nfunnel .right-content .funnelrow .cell.white-bg{
      outline:solid 1px #C9C9C9;
      background-color:white;
  }

  #divFunnel .nfunnel .right-content .funnelrow .cell.gray-bg{
      outline:solid 1px #E6E6E6;
      background-color:#E6E6E6;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block{
     position: relative;
     top: 10px;
     font-size:11px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .arpuu.up{
      color: #0CC63D;
      font-weight: bold;
      position: relative;
      font-size: 13px;
      top: 32px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .arpuu.down{
     color: #F62E30;
     font-weight: bold;
      position: relative;
      font-size: 13px;
      top: 32px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .up{
    color: #0CC63D;
    font-weight: bold;
    position: relative;
    font-size: 13px;
    top: 8px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .down{
     color: #F62E30;
     font-weight: bold;
      position: relative;
      font-size: 13px;
      top: 8px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block b{
      font-size:20px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .lower-cell{
    font-size: 13px;
      line-height: 11px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .lower-cell.smaller{
      font-size: 13px;
      margin-top: 10px;
  }


  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .colored-box{
    width: 47%;
    color: white;
    font-size: 22px;
    line-height: 44px;
    font-weight: bold;
    float:left;
    margin-left:10px;
    margin-top: 18px;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .colored-box.positive{
    background-color: #0CC63D;
  }

  #divFunnel .nfunnel .right-content .inner-numbers .funnelrow .cell .text-block .colored-box.negative{
    background-color: #F62E30;
  }

  #divFunnel .nfunnel .right-content .bottom-funnel .cell.light-blue{
    
  }

  #divFunnel .nfunnel .right-content .bottom-funnel .cell.gray-bg .text-block{
      font-size: 10px;
      position: relative;
      top: 7px;
  }

  #divFunnel .nfunnel .right-content .line{
      display: inline-block;
      width: 100%;
  }

  #divFunnel .nfunnel .right-content .cell.light-blue .line.title{
      float: inherit;
  }

  #divFunnel .nfunnel .right-content .cell.light-blue .line.title .key{
      float: inherit;
  }

  #divFunnel .nfunnel .right-content .cell.light-blue .line.title .value{
      float: inherit;
  }

  #divFunnel .nfunnel .right-content .line.title{
      margin-bottom:15px;
  }

  #divFunnel .nfunnel .right-content .line .key{
      float:left;
      margin-left:10px;
  }

  #divFunnel .nfunnel .right-content .line .value{
      margin-right: 10px;
      float: right;
  }

  /* Admin - AB Funnel END */
</style>



<?php 
function getGroupHeader($sampleGroupData, $isDisabledGroup = false){
  $css = $isDisabledGroup ? 'disabled' : '';
  
  $groupName = $sampleGroupData['group'];
  $users = number_format($sampleGroupData['unique_visitors_count'], 0);
  $conversion = $sampleGroupData['unique_visitors_percentage'];

  $id = getRullHtmlId($sampleGroupData['rule_name']);
  
  return <<<HTML
    <div class="right-square $css $id">
        <div class="content">
        <b>$groupName</b>
        <br>
        <i>USERS: $users</i>
        <br>
        <br>
        <b>$conversion%</b>
      </div>
    </div>
HTML;
}
function getPercentageCell($cellId, $total, $netotiate, $organic, $isDisabledGroup = false, $tooltipValue = null){
  $cssClass = $isDisabledGroup ? 'light-blue' : 'white-bg';
  $directionCss = '';
  $directionContent = '';
  
  if(!$isDisabledGroup && $tooltipValue != null){
    if($tooltipValue < 0){
      $directionCss = 'down';
      $formatted = number_format($tooltipValue, 2);
      $directionContent = "$formatted% &#8595;";
    }else{
      $directionCss = 'up';
      $formatted = number_format($tooltipValue, 2);
      $directionContent = "$formatted% &#8593;";
    }
  }
  
  return <<<HTML
    <div class="cell {$cssClass} $cellId">
      <div class="text-block" >
        
        <div class="line title">
          <div class="key"><b>$total%</b></div>
          <div class="value"><span class="$directionCss">$directionContent</span></div>
        </div>
        
        <div class="line lower-cell">
          <div class="key">Personali</div>
          <div class="value">$netotiate%</div>
        </div>
        <div class="line lower-cell">
          <div class="key">Organic</div>
          <div class="value">$organic%</div>
        </div>
      </div>
    </div>
HTML;
}
function getCurrencyCell($cellId, $total, $netotiate, $organic, $isDisabledGroup = false, $currency, $tooltipValue = null){
  $cssClass = $isDisabledGroup ? 'light-blue' : 'white-bg';
  
  $directionCss = '';
  $directionContent = '';
  
  if(!$isDisabledGroup && $tooltipValue != null){
    if($tooltipValue < 0){
      $directionCss = 'down';
      $formatted = number_format($tooltipValue, 2);
      $directionContent = "$formatted% &#8595;";
    }else{
      $directionCss = 'up';
      $formatted = number_format($tooltipValue, 2);
      $directionContent = "$formatted% &#8593;";
    }
  }
  
  return <<<HTML
    <div class="cell {$cssClass} $cellId">
      <div class="text-block">
        <div class="line title">
          <div class="key"><b>$currency$total</b></div>
          <div class="value"><span class="$directionCss">$directionContent</span></div>
        </div>
        <div class="line lower-cell">
          <div class="key">Personali</div>
          <div class="value">$currency$netotiate</div>
        </div>
        <div class="line lower-cell">
          <div class="key">Organic</div>
          <div class="value">$currency$organic</div>
        </div>
      </div>
    </div>
HTML;
}

function getCurrencyShiftedSalesCell($cellId, $shiftedSales, $avgDiscount, $totalImpact, $ARPUUImpact, $isDisabledGroup = false, $currency){

  $directionCss = '';
  $directionContent = '';

  if($isDisabledGroup){
    return;
  }
  
  return <<<HTML
    <div class="cell white-bg $cellId">
      <div class="text-block lower-cell smaller">
        <div class="line">
          <div class="key">Shifted sales:</div>
          <div class="value">$shiftedSales</div>
        </div>
        <div class="line">
          <div class="key">Average discount:</div>
          <div class="value">$avgDiscount%</div>
        </div>
           
        <div class="line">
          <div class="key">Total impact:</div>
          <div class="value">$currency$totalImpact</div>
        </div>

        <div class="line">
          <div class="key">ARPUU impact:</div>
          <div class="value">$currency$ARPUUImpact</div>
        </div>
      </div>
    </div>
HTML;
}

function getARPUUCell($cellId, $arpuuDiff, $isDisabledGroup = false, $currency, $tooltipValue=null){
  $boxCss = $arpuuDiff > 0 ? 'positive' : 'negative';
  
  if(!$isDisabledGroup && $tooltipValue != null){
    if($tooltipValue < 0){
      $directionCss = 'down';
      $formatted = number_format($tooltipValue, 2);
      $directionContent = "$formatted% &#8595;";
    }else{
      $directionCss = 'up';
      $formatted = number_format($tooltipValue, 2);
      $directionContent = "$formatted% &#8593;";
    }
  }
  
  if($isDisabledGroup){
    return "<div class=\"cell light-blue $cellId\"></div>";
  }
  
  $arpuuDiffFormatted = number_format($arpuuDiff, 2);
  return <<<HTML
    
    <div class="cell white-bg $cellId">
      <div class="text-block" >
        <div class="colored-box $boxCss">
          $currency$arpuuDiffFormatted
        </div>
        <span class="arpuu $directionCss">$directionContent</span>
      </div>
    </div>
HTML;
}

function getFunnelCell($cellId, $uv, $imp, $clicks, $offers, $sales, $uvImp, $impClicks, $clicksOffers, $salesOffers, $isDisabledGroup = false){
  $cssClass = $isDisabledGroup ? 'light-blue' : 'gray-bg';
  
  if($isDisabledGroup){
    return "<div class=\"cell $cssClass $cellId\"></div>";
  }
  
  return <<<HTML
    <div class="cell {$cssClass} $cellId">
      <div class="text-block" >
        <div class="line">
          <div class="key">Visitors:</div>
          <div class="value">$uv</div>
        </div>
        <div class="line">
          <div class="key">Impressions:</div>
          <div class="value">$imp ($uvImp%)</div>
        </div>
        <div class="line">
          <div class="key">Clicks:</div>
          <div class="value">$clicks ($impClicks%)</div>
        </div>
        
        <div class="line">
          <div class="key">Offers:</div>
          <div class="value">$offers ($clicksOffers%)</div>
        </div>
        
        <div class="line">
          <div class="key">Sales:</div>
          <div class="value">$sales ($salesOffers%)</div>
        </div>
      </div>
    </div>
HTML;
}

function getRullHtmlId($ruleName){
  return preg_replace('/[^\da-z]/i', '', $ruleName);
}
 

?>
<div class="funnelrow" style="width:200px;">
  <input type="text" class="form-control datepicker" name="daterange" value=""/>
</div>
<form method="get" target="" class="form-inline" id="ab-report-control-panel" role="form">
<div class="form-group">
  <select onchange="abTestNames.click(this);" name="groupSelect" id="groupSelect" class="form-control">
  <?php

  $ruleNames = array();
  
  foreach ($data as $rulename => $ruleSampleGroups){

    $id = getRullHtmlId($rulename);
    
    $firstGroup = reset($ruleSampleGroups);
    
    $start = $firstGroup['rule_start_date'];
    $end = $firstGroup['rule_end_date'];

    $ruleNames[$rulename] = array('start' => $start, 'end' => $end, 'id' => $id);
  }

  $selected = "selected='selected'";
  
  foreach ($ruleNames as $key => $value){
    echo <<<OPTIONS
      <option {$selected} value="{$value['id']}"><b>[$key] - </b> start: {$value['start']} end: {$value['end']}]</option>;
OPTIONS;
    $selected = '';
  }
  ?>

  </select>
  <select onchange='abTestNames.toggeleAdvanced(this.value)' name="toggeleAdvanced" id="toggeleAdvanced" class="form-control">
    <option value='simple'>Simple View</option>
    <option value='advanced'>Advanced View</option>
  </select>
  <select name="usdFormat" class="form-control" onchange="javascript:$('#ab-report-control-panel').submit();">
    <option value='true' <?php echo $usdFormat=='true' ? "selected='selected'" : ""?>>USD Format</option>
    <option value='false' <?php echo $usdFormat=='false' ? "selected='selected'" : ""?>>Local Currency</option>
  </select>
  <input type="hidden" class='hidden-start' name="start" value=""/>
  <input type="hidden" class="hidden-end" name="end" value=""/>
  <input type="submit" style="visibility: hidden;">
  </div>
</form>

<script type="text/javascript">
window.abTestNames = {
  init : function(){
    this.hideAllGroups();

    var ruleToShowByDefault = $("#groupSelect").find("option").first().attr('value');
    $('div.' + ruleToShowByDefault).show();
  
    var ruleNameToShowByDefault = $("#groupSelect").find("option").first().text();
    $("button.ui-multiselect").find('span:eq(1)').text(ruleNameToShowByDefault);
  },
  click : function(ui){
    this.hideAllGroups();
    $('div.' + ui.value).fadeIn();
  },
  hideAllGroups : function(){
    <?php 
      foreach ($ruleNames as $key => $value){
        echo "$('div.{$value['id']}').hide();";
      }
    ?>
  },
  toggeleAdvanced : function(value) {
      var fix_height = function() {
        $('#divFunnel .nfunnel .right-content,#divFunnel .nfunnel .left-Row').css('height','auto');
      } 

      if(value == "simple")
      {
        $('.advanced').slideUp( "slow", function() { fix_height();});
      }
      else 
      {
        $('.advanced').slideDown("slow", function() { fix_height();});
      }
    },
    usdFormat : function(){
      if($('.checkbox input:checked').length){
        //usd is selected
      }else{
        //local currently
      }
    }
};

</script>
<?php 
if(count($data) > 0){
?>
<div id="divFunnel">
<div class="nfunnel">
  <div class="left-Row">
    <div class="top-left-corner">
      <div class="test-group">
        <span>
          TEST
          <br>
          GROUP
        </span>
      </div>
    </div>
    
    <div class="left-square">
      <span class="text-block">
        REVENUE
      </span>
    </div>
    <div class="left-square">
      <span class="text-block">
        SALES
      </span>
    </div>
    <div class="left-square">
      <span class="text-block">
        CONVERSION
      </span>
    </div>
    <div class="left-square">
      <span class="text-block">
        ARPUU
      </span>
    </div>
    <div class="left-square advanced">
      <span class="text-block">
        SHIFTED SALES
      </span>
    </div>
    <div class="left-square advanced">
      <span class="text-block">
        ARPUU UPLIFT
      </span>
      <div class="subtext">
        Including shifted sales impact
      </div>
    </div>
    <div class="left-square advanced">
      <span class="text-block">
        PERSONALI FUNNEL
      </span>
    </div>
  </div>
  <div class='right-content'>
    <div class="top-groups">
      <?php 
      foreach ($data as $rulename => $ruleSampleGroups){
        foreach ($ruleSampleGroups as $key => $sampleGroupData){
          echo getGroupHeader($sampleGroupData, $sampleGroupData['is_disabled_group']);
        }
      }
      
      ?>
    </div>
    <div class="inner-numbers">
      <div class="funnelrow">
      <?php 
        foreach ($data as $rulename => $ruleSampleGroups){
          foreach ($ruleSampleGroups as $key => $sampleGroupData){
            $id = getRullHtmlId($sampleGroupData['rule_name']);
            
            $organicRevenueSum = number_format($sampleGroupData['organic_revenue_sum'], 2);
            $netotiateRevenueSum = number_format($sampleGroupData['netotiate_revenue_sum'], 2);
            $revenue = number_format($sampleGroupData['revenue'], 2);
            
            echo getCurrencyCell($id, $revenue, $netotiateRevenueSum, $organicRevenueSum, $sampleGroupData['is_disabled_group'], $sampleGroupData['currency']);
          }
        }
      ?>
      </div>    
      <div class="funnelrow">
      <?php 
        foreach ($data as $rulename => $ruleSampleGroups){
          foreach ($ruleSampleGroups as $key => $sampleGroupData){
            $id = getRullHtmlId($sampleGroupData['rule_name']);
  
            $organicSales = number_format($sampleGroupData['conversion_organic_count'], 0);
            $netotiateSales = number_format($sampleGroupData['conversion_netotiate_count'], 0);
            $total = number_format($sampleGroupData['total_sales'], 0);
            
            echo getCurrencyCell($id, $total, $netotiateSales, $organicSales, $sampleGroupData['is_disabled_group'], '');
          }
        }
      ?>
      </div>
      <div class="funnelrow">
      <?php 
        foreach ($data as $rulename => $ruleSampleGroups){
          foreach ($ruleSampleGroups as $key => $sampleGroupData){
            $id = getRullHtmlId($sampleGroupData['rule_name']);
  
            $total = $sampleGroupData['conversion_percentage'];
            $netotiate = $sampleGroupData['conversion_netotiate_percentage'];
            $organic = $sampleGroupData['conversion_organic_percentage'];
  
            $baselineConversion = $sampleGroupData['baseline_conversion'] > 0 ? $sampleGroupData['baseline_conversion'] : 1;
            $tooltip = (($total - $baselineConversion)/$baselineConversion) * 100;
            
            echo getPercentageCell($id, $total, $netotiate, $organic, $sampleGroupData['is_disabled_group'], $tooltip);
          }
        }
      ?>
      </div>  
      <div class="funnelrow">
      <?php 
        foreach ($data as $rulename => $ruleSampleGroups){
          foreach ($ruleSampleGroups as $key => $sampleGroupData){
            $id = getRullHtmlId($sampleGroupData['rule_name']);
            
            $total = $sampleGroupData['ARPUU'];
            $netotiate = $sampleGroupData['netotiate_ARPUU'];
            $organic = $sampleGroupData['organic_ARPUU'];
      
            $organicARPUU = $sampleGroupData['baselineARPUU'] > 0 ? $sampleGroupData['baselineARPUU'] : 1;
            $tooltip = (($total - $organicARPUU) / $organicARPUU) * 100;
            
            echo getCurrencyCell($id, number_format($total, 2), number_format($netotiate, 2), number_format($organic, 2), $sampleGroupData['is_disabled_group'], $sampleGroupData['currency'], $tooltip);
          }
        }
      ?>
      </div>  
      <div class="funnelrow advanced">
      <?php 
        //Shifted sales
        foreach ($data as $rulename => $ruleSampleGroups){
          foreach ($ruleSampleGroups as $key => $sampleGroupData){
            $id = getRullHtmlId($sampleGroupData['rule_name']);
  
            if($sampleGroupData['is_disabled_group']){
              echo "<div class=\"cell light-blue $id\"></div>";
              continue;
            }
  
            $shiftedSales = $sampleGroupData['shiftedSales'];
            $avgDiscount = $sampleGroupData['avg_discount']*100;
            $totalImpact = $sampleGroupData['totalImpact'];
            $ARPUUImpact = number_format($sampleGroupData['ARPUUImpact'], 3);
    
            echo getCurrencyShiftedSalesCell($id, number_format(round($shiftedSales), 0), number_format($avgDiscount, 2), number_format($totalImpact, 2), $ARPUUImpact, $sampleGroupData['is_disabled_group'], $sampleGroupData['currency']);
          }
        }
      ?>
      </div>  
      <div class="funnelrow advanced">
        <?php 
          foreach ($data as $rulename => $ruleSampleGroups){
            foreach ($ruleSampleGroups as $key => $sampleGroupData){
              $id = getRullHtmlId($sampleGroupData['rule_name']);
        
              $total = $sampleGroupData['ARPUU'] + $sampleGroupData['ARPUUImpact'];
              $netotiate = $sampleGroupData['netotiate_ARPUU'];
              $organic = $sampleGroupData['organic_ARPUU'];
              
              $organicARPUU = $sampleGroupData['baselineARPUU'] > 0 ? $sampleGroupData['baselineARPUU'] : 1;
              $tooltip = (($total - $organicARPUU) / $organicARPUU) * 100;
  
              echo getARPUUCell($id, $sampleGroupData['additional_revenue_per_user'], $sampleGroupData['is_disabled_group'], $sampleGroupData['currency'], $tooltip);
            }
          }
        ?>
      </div>  
    </div>
    <div class="bottom-funnel funnelrow advanced">
      <?php 
        foreach ($data as $rulename => $ruleSampleGroups){
          foreach ($ruleSampleGroups as $key => $sampleGroupData){
            $id = getRullHtmlId($sampleGroupData['rule_name']);
            $uv = $sampleGroupData['unique_visitors_count'];
            $imp = $sampleGroupData['impressions'] > 0 ? $sampleGroupData['impressions'] : 1;
            $clicks = $sampleGroupData['clicks'] > 0 ? $sampleGroupData['clicks'] : 1;
            $offers = $sampleGroupData['offers'] > 0 ? $sampleGroupData['offers'] : 1;
            $sales = $sampleGroupData['sales'];
            
            $uvImp = number_format(($imp / $uv) * 100, 2);
            $impClicks = number_format(($clicks / $imp) * 100, 2);
            $clicksOffers = number_format(($offers / $clicks) * 100, 2);
            $salesOffers = number_format(($sales / $offers) * 100, 2); 
            
            echo getFunnelCell( $id, 
                      number_format($uv, 0), 
                      number_format($imp, 0), 
                      number_format($clicks, 0), 
                      number_format($offers, 0), 
                      number_format($sales, 0),
                      $uvImp,
                      $impClicks,
                      $clicksOffers,
                      $salesOffers, 
                      $sampleGroupData['is_disabled_group']);
          }
        }?>
    </div>
  </div>
</div>
</div>
<?php 
}
?>
@endsection

@section('custom-javascript')
<script type="text/javascript" src="{{ url('scripts/moment.js') }}"></script>
<script type="text/javascript" src="{{ url('scripts/daterangepicker.js') }}"></script>

<script type="text/javascript">
    $(function() {
        var startDate = moment("{{ $startDate }}","YYYY-MM-DD").format('MM/DD/YYYY');
        var endDate = moment("{{ $endDate }}","YYYY-MM-DD").format('MM/DD/YYYY');

        var defaultDateRange = startDate + ' - ' + endDate;

        $('input[name="daterange"]').val(defaultDateRange);

        $('input[name="daterange"]').daterangepicker({'maxDate' : moment(new Date().getTime())}, function(start, end){
          var urlParams = $.url().data.param.query;
          //console.log(urlParams);

          urlParams.start = start.format('YYYY-MM-DD');
          urlParams.end = end.format('YYYY-MM-DD');

          var baseUrl = $.url().data.attr.base + $.url().data.attr.path;
          // console.log(baseUrl);
          window.location.href = baseUrl + '?' + $.param(urlParams);
        });

        $('.hidden-start').val(moment("{{ $startDate }}","YYYY-MM-DD").format('YYYY-MM-DD'));
        $('.hidden-end').val(moment("{{ $endDate }}","YYYY-MM-DD").format('YYYY-MM-DD'));
    });

  abTestNames.init();
</script>
@endsection

