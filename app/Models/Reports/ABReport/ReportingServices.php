<?php
namespace App\Models\Reports\ABReport;

use App\Models\Reports\ABReport as ReportsModel;

class ReportingServices {
	private $filters = Array();
	private $monthly_start = '2013-01-01';
	private $monthly_end =  Null;
	private $currency_rate = 1;
	private $admin = false;

	public function __construct($filters, $admin = false) {
		$this->filters = $filters;
		$this->monthly_end =  date("Y-m-d");
        $this->currency_rate = 1;
        $this->admin = $admin;
        
        if($this->admin && $filters['usdFormat'] == 'true')
            $this->currency_rate = 'currency_rate';
	}
	
	public function getFilters(){
		return $this->filters;
	}

	private function _currency(){
		return str_replace('&nbsp;', ' ',  _t('ProductArena.$'));
	}
	
	public function getAffilateId($retailerId){
		$sqlQuery = 'select affiliate_id from netotiate.affiliate_retailers where retailer_id = '.$retailerId . ';';		
		$model = new ReportsModel();
		$ds = $model->executeQuery($sqlQuery);
		if(isset($ds) && sizeof($ds)>0)
			return $ds[0]['affiliate_id'];
		else return 1;
		
	}

	private $locale_mapping = array(
								'checkout' =>'ProductArena.in_process',
								'expired' => 'ProductArena.expired',
								'new' => 'ProductArena.new',
								'purchased' => 'ProductArena.purchased', 
								'accepted' => 'ProductArena.accepted', 
								'counterOffer'=>'ProductArena.counter_offer',
								'declined'=>'ProductArena.declined'
								);

	private function fixDateRange($ds,$fromDate,$toDate,$columns,$format='m/d/y',$days=1,$the_date='the_date'){
		$arrFrom = explode('-', $fromDate);		
		$arrTo = explode('-', $toDate);
		if(sizeof($arrFrom)!=3 || sizeof($arrTo)!=3)return $ds;

		$arr = $this->createDateRangeArray($arrFrom , $arrTo,$format,$days); 
		
		if(sizeof($arr) == sizeof($ds)) return $ds;
		
		$new_ds = array();
		foreach (array_values($arr) as $row)
		{
			$val = $this->findRows($ds, $row,$the_date);
			if(sizeof($val) > 0)
				foreach($val as $r)
					array_push( $new_ds, $r);
			else
			{
				$val = array($the_date => $row);
				foreach($columns as $key => $value)
					$val[$key] = 0;
				
				array_push( $new_ds, $val);
			}
		}
		
		return $new_ds;
	}
	
	function findRows($ds, $the_date,$col_name)
	{
		$result = array();
		foreach($ds as $row)
		{
			if($row[$col_name] == $the_date)
				array_push($result, $row);
		}
		return $result;
	}
	
	
	function createDateRangeArray($arrFrom,$arrTo,$format,$days=1){
		// takes two dates formatted as YYYY-MM-DD and creates an
		// inclusive array of the dates between the from and to dates.
	
		// could test validity of dates here but I'm already doing
		// that in the main script
		
		$aryRange=array();
	
		$iDateFrom=mktime(1,0,0,$arrFrom[1],$arrFrom[2],$arrFrom[0]);
		$iDateTo=mktime(1,0,0,$arrTo[1],$arrTo[2],$arrTo[0]);
		
		if ($iDateTo>=$iDateFrom)
		{
			array_push($aryRange,date($format,$iDateFrom)); // first entry
			while ($iDateFrom<$iDateTo)
			{
				$delta = $days * 86400; //24 hours
				if(($iDateFrom+$delta) < $iDateTo)
					$iDateFrom+=$delta;
				else
					$iDateFrom+=($iDateTo - $iDateFrom);
				array_push($aryRange,date($format,$iDateFrom));
			}
		}
		return $aryRange;
	}
	
	
	public function getDashboard(){
		$subQuery =  $this->getSubQuery();
		
		$groupBy = ' group by the_date order by the_date asc;';
		
		if($this->isAdvancedFiltersOn())//either device_type or category
            $sqlQuery = "select DATE_FORMAT(the_date, '%m/%d/%y') as the_date, sum((purchases+netotiate_canceled)) as sales , round((sum(netotiate_revenue_sum*$this->currency_rate)),2) as revenue, round(sum(netotiate_cancel_sum*$this->currency_rate),2) as canceledSale from dwh.daily_category_funnel WHERE TRUE ";
        else
            $sqlQuery = "select DATE_FORMAT(the_date, '%m/%d/%y') as the_date, sum((purchases+canceled)) as sales , round((sum(purchase_sum*$this->currency_rate)),2) as revenue, round(sum(cancel_sum*$this->currency_rate),2) as canceledSale from dwh.daily_funnel WHERE TRUE";

		$sqlQuery.= $subQuery. $groupBy;

		$model = new ReportsModel();

		$ds = $model->executeQuery($sqlQuery);
		$seriesColumnsList = array('sales'=> _t('ProductArena.sales') ,'revenue'=> _t('ProductArena.revenue'), 'canceledSale'=> _t('ProductArena.canceled') );
		$ds = $this->fixDateRange($ds,$this->filters['fromDate'],$this->filters['toDate'],array('sales'=>0,'revenue'=>0, 'canceledSale'=> 0));
		$ds = $this->localizeDate($ds, 'the_date');
		$result = $model->transformColumnsSeriesToChart($ds, null, $this->_currency(), 'the_date' , $seriesColumnsList ,_t("ProductArena.sales_and_revenue"), "",_t("ProductArena.number_of_sales"),$this->_currency(),'');

		if(sizeof($result['dataset']) == 3){
			$result['chart']['pyaxisname'] = _t('ProductArena.revenue');
			$result['chart']['syaxisname'] = _t('ProductArena.sales');		
			$result['chart']["labelDisplay"]="ROTATE";
			$result['chart']["slantLabels"]="1";
			if(sizeof($ds) <= 10)
				$result['chart']['showsum'] = '1';
			else $result['chart']['showsum'] = '0';
			
			$result['dataset'][2]['alpha'] = '50';			
			$result['dataset'][2]['color'] = 'EDEDED';
			$result['chart']['decimals'] = '0';
			$result['chart']['sFormatNumberScale'] = '0';	
		}
		
		$result2 = array('chart'=>$result['chart'], 'categories' => $result['categories'] ,'dataset' => array( array('dataset' => array($result['dataset'][1],$result['dataset'][2]))) , 'lineset'=> $result['dataset'][0] );
		
		$response = array( array('chartResult'=>$result2,'chartType'=> 'MSStackedColumn2DLineDY','size'=>1));
		
		//Unique Visitors and Offers
        if($this->isAdvancedFiltersOn())
            $sqlQuery = "select DATE_FORMAT(the_date, '%m/%d/%y') as the_date, sum(unique_visitors) as visitors , sum(submitted_offers) as submitted from dwh.daily_category_funnel WHERE TRUE ";
        else
            $sqlQuery = "select DATE_FORMAT(the_date, '%m/%d/%y') as the_date, sum(visitors) as visitors , sum(submitted) as submitted from dwh.daily_funnel WHERE TRUE ";
			
		$sqlQuery.= $subQuery . $groupBy;

		$ds = $model->executeQuery($sqlQuery);
		$seriesColumnsList = array('visitors'=> _t('ProductArena.unique_visitors') ,'submitted'=> _t('ProductArena.offers') );		
		$ds = $this->fixDateRange($ds,$this->filters['fromDate'],$this->filters['toDate'],array('visitors'=>0,'submitted'=>0));
		$ds = $this->localizeDate($ds, 'the_date');
		$result = $model->transformColumnsSeriesToChart($ds, null, $this->_currency(), 'the_date' , $seriesColumnsList ,_t("ProductArena.unique_visitors_and_offers"), "","");
		
		if(sizeof($result['dataset']) == 2){
			$result['dataset'][0]['parentyaxis'] = "P";
			$result['dataset'][1]['parentyaxis'] = "S";
			$result['chart']['syaxisname'] = _t('ProductArena.offers');
			$result['chart']['pyaxisname'] = _t('ProductArena.unique_visitors');
			$result['dataset'][0]['renderAs'] = 'Line';
			$result['dataset'][1]['renderAs'] = 'Column';
			$result['chart']["labelDisplay"]="ROTATE";
			$result['chart']["slantLabels"]="1";
			$result['chart']['showsum'] = '1';
			$result['chart']['decimals'] = '0';
			$result['chart']['sFormatNumberScale'] = '0';
			
		}

		array_push($response , array('chartResult'=>$result,'chartType'=> 'MSCombiDY2D','size'=>1));	
		return $response;
	}
	
	public function getOffers(){
		$model = new ReportsModel();
		$subQuery =  str_replace ('the_date' , 'create_date', $this->getSubQuery());
		
		//Offers per category
		$groupBy = ' group by the_date,category_ order by create_date asc';
		$sqlQuery = "select affiliate_id, DATE_FORMAT(create_date, '%m/%d/%y') as the_date, IF(category IS null or category = '' or category = 'null' , 'N/A',  category) as category_, count(id) as offers from netotiate.transactions WHERE TRUE $subQuery $groupBy";
		
		$final_sql = 'select  the_date, IF(ac.category_name IS NULL , r.category_ , ac.category_name) as category_  ,
				offers  from (' . $sqlQuery . ') as r left join affiliates_categories ac on r.affiliate_id = ac.affiliate_id and r.category_ = ac.category_id group by the_date,category_ order by the_date asc;';
		
		$ds = $model->executeQuery($final_sql);
		$ds = $this->fixDateRange($ds,$this->filters['fromDate'],$this->filters['toDate'],array('offers'=>0,'category_'=>'N/A'));
		$ds = $this->localizeDate($ds, 'the_date');
		$result = $model->transformSeriesToChart($ds, null, 'offers', 'the_date' , 'category_',_t("ProductArena.daily_offers_submitted_per_category"), _t("ProductArena.date"),_t("ProductArena.number_of_offers"));
		
		$result['chart']["labelDisplay"]="ROTATE";
		$result['chart']["slantLabels"]="1";
		$result['chart']["showsum"]="1";
		$result['chart']['plotGradientColor'] = '';
		$result['chart']['paletteColors'] = '1ba1e2,e51400,a2c139,d80073,339933,f09609,e671b8,00aba9,a05000';
		$result['chart']['decimals'] = '0';
		$response = array( array('chartResult'=>$result,'chartType'=> 'StackedColumn2D','size'=>1));
		
		$groupBy = ' group by retailer_status,consumer_status order by retailer_status asc;';
        $sqlQuery = "select count(id) as count , retailer_status,consumer_status from netotiate.transactions WHERE TRUE $subQuery $groupBy";

		$ds = $model->executeQuery($sqlQuery);
		$this->replaceValue('consumer_status', 'checkout', _t( $this->locale_mapping['checkout'] ), $ds);
		$this->replaceValue('consumer_status', 'expired', _t( $this->locale_mapping['expired'] ), $ds);
		$this->replaceValue('consumer_status', 'new', _t( $this->locale_mapping['new'] ), $ds);
		$this->replaceValue('consumer_status', 'purchased', _t( $this->locale_mapping['purchased'] ), $ds);
		
		$this->replaceValue('retailer_status', 'accepted', _t( $this->locale_mapping['accepted'] ), $ds);
		$this->replaceValue('retailer_status', 'new', _t( $this->locale_mapping['new'] ), $ds);
		$this->replaceValue('retailer_status', 'expired', _t( $this->locale_mapping['expired'] ), $ds);
		$this->replaceValue('retailer_status', 'declined', _t( $this->locale_mapping['declined'] ), $ds);
		$this->replaceValue('retailer_status', 'counterOffer', _t( $this->locale_mapping['counterOffer'] ), $ds);

		
		$result = $model->transformSeriesToChart($ds, null, 'count', 'retailer_status' , 'consumer_status',_t("ProductArena.customer_status_per_response_type"), _t("ProductArena.retailer_response"),_t("ProductArena.number_of_offers"));
		
		if(sizeof($result) >1)
		{
			$result['chart']["showPercentValues"]="1";
			$result['chart']["legendCaption"]=_t("ProductArena.customer_status");
			$result['chart']["showsum"]="1";
			$result['chart']['paletteColors'] = '1ba1e2,e51400,a2c139,d80073,339933,f09609,e671b8,00aba9,a05000';
			$result['chart']['plotGradientColor'] = '';
			$result['chart']['decimals'] = '0';
				
		}
		array_push($response, array('chartResult'=>$result,'chartType'=> 'StackedBar2D','size'=>1));
		return $response;
		
	}
	
	public function getFeeChart(){
		$subQuery =  $this->getSubQuery();
		$groupBy = ' group by the_date order by the_date asc;';
				
        $sqlQuery = <<<QUERY
                SELECT DATE_FORMAT(the_date, '%m/%d/%y') as the_date,
                                sum( netotiate_fee_sum * {$this->currency_rate}) as fee
                FROM dwh.daily_category_funnel
                WHERE TRUE $subQuery $groupBy
QUERY;

		$model = new ReportsModel();
		
		$ds = $model->executeQuery($sqlQuery);
		$ds = $this->fixDateRange($ds,$this->filters['fromDate'],$this->filters['toDate'],array('fee'=>0));

		$result = $model->transformToSingleSeriesChart($ds, 'the_date', 'fee', '', _t('ProductArena.commission'), _t('ProductArena.netotiate_commission'));

		$result['chart']['numberPrefix'] = $this->_currency();
		if(sizeof($ds) <= 10)
			$result['chart']["showvalues"]="1";
		else 
			$result['chart']["showvalues"]="0";
		$result['chart']["labelDisplay"]="ROTATE";
		$result['chart']["slantLabels"]="1";
		$result['chart']['decimals'] = '0';
		
		$response = array('chartResult'=>$result,'chartType'=> 'Column2D','size'=>1);
		return $response;
	}
	
	
	public function getMonthlyFeeChart(){
		$subQuery =  $this->getSubQuery(array('date' => false));
	
		$groupBy = ' group by the_month order by the_date asc;';

		$sqlQuery = <<<QUERY
				select 	DATE_FORMAT(the_date, '%m/%y') as the_month, 
						sum(netotiate_fee_sum*$this->currency_rate) as fee  
				from dwh.daily_category_funnel 
				WHERE TRUE 
QUERY;
		
		$sqlQuery.= $subQuery. $groupBy;
		$model = new ReportsModel();
		$ds = $model->executeQuery($sqlQuery);
		
		$result = $model->transformToSingleSeriesChart($ds, 'the_month', 'fee', '', _t('ProductArena.commission'), _t('ProductArena.netotiate_commission'));
		$result['chart']['numberPrefix'] = $this->_currency();
		
		$result['chart']["showvalues"]="1";
		
		$result['chart']["labelDisplay"]="ROTATE";
		$result['chart']["slantLabels"]="1";
		$result['chart']['decimals'] = '0';
	
		$response = array('chartResult'=>$result,'chartType'=> 'Column2D','size'=>1);
		return $response;
	}
	
	
	private function replaceValue($column,$oldValue,$value,&$ds){
		foreach($ds as  &$item)
		{
			if($item[$column] == $oldValue)
				$item[$column] = $value;
		}
	}
	
	private function formatToPrecentage($num,$total){
		$result = 0; 
		if( $total > 0 )
		{
			$result = round($num/$total * 100,2);
			 
		}
		return $result;
	}
	
	public function getFunnel(){
		$model = new ReportsModel();
		$subQuery =  $this->getSubQuery();
		
		$sqlQuery = <<<QUERY
				SELECT  sum(unique_visitors) as visitors, 
						sum(clicks) as clicks , 
						sum(submitted_offers) as submitted , 
						sum(purchases) as purchases  
				FROM 	dwh.daily_category_funnel  
				WHERE 	TRUE 
QUERY;
		
		$sqlQuery.= $subQuery ;
		$ds = $model->executeQuery($sqlQuery);
		
		$visitorsModel = new UniqueVisitors();
		$uniqueVisitors = $visitorsModel->getByAffiliateId($this->filters);

		if(sizeof($ds) == 1){
			$rows = array();
			
			array_push($rows, array("name"=>_t('ProductArena.unique_visitors').' ('. number_format($uniqueVisitors).')' , "value"=> 100));
			array_push($rows, array("name"=>_t('ProductArena.button_widget_clicks').' ('.number_format($ds[0]['clicks']).')'  , "value" =>$this->formatToPrecentage($ds[0]['clicks'], $ds[0]['visitors']) ));
			array_push($rows, array("name"=>_t('ProductArena.offer_submitted').' ('. number_format($ds[0]['submitted']).')'  , "value" =>$this->formatToPrecentage($ds[0]['submitted'], $ds[0]['visitors']) ));
			array_push($rows, array("name"=>_t('ProductArena.purchased_items').' ('. number_format($ds[0]['purchases']).')'  , "value" =>$this->formatToPrecentage($ds[0]['purchases'], $ds[0]['visitors']) ));

			$result = $model->transformToSingleSeriesChart($rows, 'name', 'value', _t('ProductArena.action'), _t('ProductArena.count'), _t('ProductArena.service_funnel'), "%");
			if(sizeof($result) >1)
			{
				$result['chart']["showvalues"]="1";
				$result['chart']["showPercentValues"]="1";
				$result['chart']["plotSpacePercent"]="30";
				$result['chart']["numberPrefix"]="";
				$result['chart']["numberSuffix"]="%";			
			}
			
			$response = array( array('chartResult'=>$result,'chartType'=> 'Bar2D','size'=>1));
			return $response;				
		}
	}
	
	public function getMonthlyReports(){
		$model = new ReportsModel();
		
		//Monthly Sales and Revenue		
		$subQuery =  $this->getSubQuery(array('date' => false));
		$groupBy = ' group by the_month order by the_date asc;';
	
		$sqlQuery = <<<QUERY
				SELECT 	DATE_FORMAT(the_date, '%m/%y') as the_month , 
						sum(purchases + netotiate_canceled ) as sales , 
						round(sum(netotiate_revenue_sum*{$this->currency_rate}),2) as revenue, 
						round(sum(netotiate_cancel_sum*{$this->currency_rate}),2) as cancel_revenue 
				FROM 	dwh.daily_category_funnel 
				WHERE TRUE 
QUERY;
		
		$sqlQuery.= $subQuery . $groupBy;
		$ds = $model->executeQuery($sqlQuery);
		
		$ds = $this->fixDateRange($ds,$this->monthly_start,$this->monthly_end,array('sales'=>0,'revenue'=>0, 'cancel_revenue'=>0),'m/y',31,'the_month');

		$seriesColumnsList = array('sales'=> _t('ProductArena.sales') ,'revenue'=> _t('ProductArena.revenue'), 'cancel_revenue' => _t('ProductArena.canceled') );
		$result = $model->transformColumnsSeriesToChart($ds, null, $this->_currency(), 'the_month' , $seriesColumnsList ,_t("ProductArena.monthly_sales_and_revenue"), "",_t("ProductArena.number_of_sales"),$this->_currency());
		//get projection data
		list( $visitors_p, $sales_p,  $revenue_p, $discount_p, $offers_p ) = $this->getCurrentMonthProjection();
		 
		if(sizeof($result['dataset']) == 3){
			$result['chart']["showsum"]="0";
			$result['chart']['sFormatNumberScale'] = "0";
			$result['chart']['syaxisname'] = _t('ProductArena.sales');
			$result['chart']['pyaxisname'] = _t('ProductArena.revenue');		
			$projection_month = (date('n') -1) ;			
			$projection_data = array();
			foreach($result['dataset'][1]['data'] as $r)
				array_push($projection_data, array('value'=> null , "dashed" => '1', 'color' => "FFFFFF" ));
			
			//Get projection sales and revenue for the current month

			$projection_data[$projection_month]['value'] = $revenue_p;
			$projection = array('seriesname'=>'Projection' , 'data' => $projection_data , 'color' => "FFFFFF" , "alpha"=> '50' , 'includeInLegend'=> '0');
			
			if($projection_month>0)
				if($result['dataset'][0]['data'][$projection_month]['value'] != null && $result['dataset'][0]['data'][$projection_month]['value'] !='')
					$sales_p+= (int)$result['dataset'][0]['data'][$projection_month]['value'];
				$result['dataset'][0]['data'][$projection_month]['value'] = $sales_p;
				$result['dataset'][0]['data'][$projection_month-1]['dashed'] = '1';
				$result['dataset'][0]['data'][$projection_month-1]['alpha'] = '60';
				$result['dataset'][0]['data'][$projection_month]['anchorBgColor'] = 'FFFFFF';
				$result['dataset'][0]['data'][$projection_month]['anchorBgAlpha'] = '60';
				$result['dataset'][0]['data'][$projection_month]['anchorAlpha'] = '60';
			
				
			$result['dataset'][2]['alpha'] = '70';			
			$result['dataset'][2]['color'] = 'EDEDED';
			
			$result2 = array('chart'=>$result['chart'], 'categories' => $result['categories'] ,'dataset' => array( array('dataset' => array($result['dataset'][1],$result['dataset'][2],$projection))) , 'lineset'=> $result['dataset'][0] );
			
		}
		$response = array( array('chartResult'=>$result2,'chartType'=> 'MSStackedColumn2DLineDY','size'=>1));
		
		//Monthly Unique Visitors and Offers
		$sqlQuery = "select DATE_FORMAT(the_date, '%m/%y') as the_month, sum(visitors) as visitors , sum(submitted) as submitted from dwh.daily_funnel WHERE TRUE ";
		
		$sqlQuery.= $subQuery . $groupBy;
		$ds = $model->executeQuery($sqlQuery);		
		$ds = $this->fixDateRange($ds,$this->monthly_start,$this->monthly_end,array('visitors'=>0,'submitted'=>0),'m/y',31,'the_month');
		
		$seriesColumnsList = array('visitors'=> _t('ProductArena.unique_visitors') ,'submitted'=> _t('ProductArena.offers') );
		
		$result = $model->transformColumnsSeriesToChart($ds, null, $this->_currency(), 'the_month' , $seriesColumnsList ,_t("ProductArena.monthly_unique_visitors_and_offers"), "","");
		
		if(sizeof($result['dataset']) == 2){
			$result['chart']["showsum"]="0";
			$result['chart']['syaxisname'] = _t('ProductArena.offers');
			$result['chart']['pyaxisname'] = _t('ProductArena.unique_visitors');
			$result['chart']['sFormatNumberScale'] = "0";			
			$projection_month = (date('n') -1) ;
				
			$projection_data = array();
			foreach($result['dataset'][0]['data'] as $r)
				array_push($projection_data, array('value'=> null , "dashed" => '1', 'color' => "FFFFFF" ));
				
				
			$projection_data[$projection_month]['value'] = $visitors_p;
			$projection = array('seriesname'=>'Projection' , 'data' => $projection_data , 'color' => "FFFFFF" , "alpha"=> '50' , 'includeInLegend'=> '0');
			$currentValue = $result['dataset'][1]['data'][$projection_month]['value'];
			if(isset($currentValue) && $currentValue > 0)
				$result['dataset'][1]['data'][$projection_month]['value'] += $offers_p;
			else 
				$result['dataset'][1]['data'][$projection_month]['value'] = $offers_p;
			
			if($projection_month>0)
				$result['dataset'][1]['data'][$projection_month-1]['dashed'] = '1';
			$result['dataset'][1]['data'][$projection_month-1]['alpha'] = '60';
				
			$result['dataset'][1]['data'][$projection_month]['anchorBgColor'] = 'FFFFFF';
			$result['dataset'][1]['data'][$projection_month]['anchorBgAlpha'] = '60';
			$result['dataset'][1]['data'][$projection_month]['anchorAlpha'] = '60';
				
			$result2 = array('chart'=>$result['chart'], 'categories' => $result['categories'] ,'dataset' => array( array('dataset' => array($result['dataset'][0], $projection))) , 'lineset'=> $result['dataset'][1] );
			
			
		}
		array_push($response , array('chartResult'=>$result2,'chartType'=> 'MSStackedColumn2DLineDY','size'=>1));
		
		//Monthly offers
		$groupBy = ' group by the_month, retailer_status  order by create_date asc;';
		$sqlQuery = "select count(id) as count , retailer_status, DATE_FORMAT(create_date, '%m/%y') as the_month from netotiate.transactions WHERE TRUE ";
		
		$date = '"'. date('Y'). '-'. date('m'). '-' . date('d') . '"';
		if($subQuery == ' ')
			$subQuery = ' and create_date <  '. $date;
		else
			$subQuery.= ' and create_date <  '. $date;
		
		$sqlQuery.= $subQuery .  $groupBy;
		$ds = $model->executeQuery($sqlQuery);
		
	    $ds = $this->fixDateRange($ds,$this->monthly_start,$this->monthly_end,array('count'=>0,'retailer_status'=>0),'m/y',31,'the_month');
	    
	    $this->replaceValue('retailer_status', 'accepted', _t( $this->locale_mapping['accepted'] ), $ds);
	    $this->replaceValue('retailer_status', 'new', _t( $this->locale_mapping['new'] ), $ds);
	    $this->replaceValue('retailer_status', 'expired', _t( $this->locale_mapping['expired'] ), $ds);
	    $this->replaceValue('retailer_status', 'declined', _t( $this->locale_mapping['declined'] ), $ds);
	    $this->replaceValue('retailer_status', 'counterOffer', _t( $this->locale_mapping['counterOffer'] ), $ds);
		
		$result = $model->transformSeriesToChart($ds, null, 'count', 'the_month' , 'retailer_status',_t("ProductArena.monthly_offer_response_type"), _t("ProductArena.month"),_t("ProductArena.number_of_offers"));
		$result['chart']['paletteColors'] = '1ba1e2,e51400,a2c139,d80073,339933,f09609,e671b8,00aba9,a05000';
		$result['chart']['plotGradientColor'] = '';
		$result['chart']["showsum"]="1";
		$result['chart']["forceDecimals"]="0";
		
		
		//Response type projection....
		//----------------------------------
		$projection_month = (date('n') -1) ;
		$projection_data = array();
		foreach($result['dataset'][0]['data'] as $r)
			array_push($projection_data, array('value'=> null , "dashed" => '1', 'color' => "FFFFFF" ));

		$projection_data[$projection_month]['value'] = round($offers_p,0);
		$projection = array('seriesname'=>'Projection' , 'data' => $projection_data , 'color' => "FFFFFF" , "alpha"=> '50' , 'includeInLegend'=> '0');			
		array_push($result['dataset'],$projection );		
		array_push($response, array('chartResult'=>$result,'chartType'=> 'StackedColumn2D','size'=>1));
		
		//Monthly discount
		$groupBy = ' group by the_month order by create_date asc;';

		$sqlQuery = "select round(((sum(deal_price)-sum(purchase_amount))/sum(deal_price)*100),2) as discount,round(((sum(deal_price)-sum(user_price))/sum(deal_price)*100),2) as discount_requested, DATE_FORMAT(create_date, '%m/%y') as the_month  from netotiate.transactions WHERE TRUE  ";
		
		$sqlQuery.= "$subQuery AND consumer_status = 'purchased' and create_date > '2013-01-01' $groupBy";
		
		$ds = $model->executeQuery($sqlQuery);
		$ds = $this->fixDateRange($ds,$this->monthly_start,$this->monthly_end,array('discount'=>0,'discount_requested'=>0),'m/y',31,'the_month');
		
		$seriesColumnsList = array('discount'=> _t('ProductArena.discount') ,'discount_requested'=> _t('ProductArena.requested_discount') );
		
		$result = $model->transformColumnsSeriesToChart($ds, null, $this->_currency(), 'the_month' , $seriesColumnsList ,_t("ProductArena.monthly_average_discount_requested_vs_closed"), "",_t("ProductArena.number_of_sales"),"");
		$result['chart']['paletteColors'] = '1ba1e2,e51400,a2c139,d80073,339933,f09609,e671b8,00aba9,a05000';
		$result['chart']['plotGradientColor'] = '';
		$result['chart']['linecolor'] = '';
		$result['chart']["showvalues"]="1";
		
		$result['chart']["rotateValues"]="1";
		$result['chart']["valuePosition"]="above";
		$result['chart']["anchorBgColor"]="";
		$result['chart']["numberSuffix"]="%";
		$result['styles'] = $this->getLine();
		
		array_push($response, array('chartResult'=>$result,'chartType'=> 'MSLine','size'=>1));
		
		return $response;
	}
	
	function getOffersProjection(){
		$subQuery = $this->getSubQuery(array('date' => false));

		$sqlQuery =  sprintf( "select sum(t.count) as offers , count(the_day) as count from (select count(id) as count ,
								DAY(create_date) as the_day  from netotiate.transactions
								where create_date >= '%s' and create_date <= '%s' %s and category='{$this->filters['category']}' group by the_day) as t;",
								date("Y-m-1", strtotime("-1 month")),	date("Y-m-t", strtotime("-1 month")), $subQuery);
		
		$model = new ReportsModel();
		$ds = $model->executeQuery($sqlQuery);
		$result = 0;
		$m_days = date("t");
		$days = date("j");
		$days_left = $m_days - $days;
		if(sizeof($ds) > 0)
		{
			if($ds[0]['count'] > 0)
				$count = $ds[0]['count'];
			else $count=1; 
			
			$result = $ds[0]['offers'] / $count * $days_left;
			
		}
		
		return $result;
	}
	
	public function getSales(){
		$response = array();
		
		$model = new ReportsModel();
		$subQuery =  $this->getSubQuery();

		$groupBy = ' group by the_date order by the_date asc;';
		
		$sqlQuery = <<<QUERY
				SELECT 	DATE_FORMAT(the_date, '%m/%d/%y') as the_date, 
						round((sum(netotiate_revenue_sum* {$this->currency_rate}) / IF(sum(purchases) > 0 , sum(purchases),  1)),2) as sales  
				FROM dwh.daily_category_funnel 
				WHERE TRUE 
QUERY;
		
		$sqlQuery.= $subQuery . $groupBy;
		
		$model = new ReportsModel();
		$ds = $model->executeQuery($sqlQuery);
		$ds = $this->fixDateRange($ds,$this->filters['fromDate'],$this->filters['toDate'],array('sales'=>0));
		$ds = $this->localizeDate($ds, 'the_date');
		$result = $model->transformToSingleSeriesChart($ds, 'the_date', 'sales', _t('ProductArena.date'), _t('ProductArena.average_ticket'), _t('ProductArena.average_ticket_per_day'), "  ".$this->_currency());
		$result['chart']["labelDisplay"]="ROTATE";
		$result['chart']["slantLabels"]="1";
		$result['chart']["showvalues"]="1";
		$result['chart']["rotateValues"]="1";
		$result['chart']["valuePosition"]="above";
		$result['styles'] = $this->getLine();
		
		array_push($response,  array('chartResult'=>$result,'chartType'=> 'Line','size'=>1));
		
		// Average discount
		$sqlQuery = "select DATE_FORMAT(the_date, '%m/%d/%y') as the_date, round((((sum(original_price_sum * $this->currency_rate) - sum(netotiate_revenue_sum * $this->currency_rate))/IF(sum(original_price_sum * $this->currency_rate) > 0 , sum(original_price_sum * $this->currency_rate),  1))*100),2)  as discount from dwh.daily_category_funnel WHERE TRUE ";
		
		$sqlQuery.= $subQuery . $groupBy;
		$ds = $model->executeQuery($sqlQuery);
		$ds = $this->fixDateRange($ds,$this->filters['fromDate'],$this->filters['toDate'],array('discount'=>0));
		$ds = $this->localizeDate($ds, 'the_date');
		$result = $model->transformToSingleSeriesChart($ds, 'the_date', 'discount', _t('ProductArena.date'), _t('ProductArena.average_discount'), _t('ProductArena.average_discount_per_day'), "");
		$result['chart']["labelDisplay"]="ROTATE";
		$result['chart']["slantLabels"]="1";
		$result['chart']["showvalues"]="1";
		$result['chart']["rotateValues"]="1";
		$result['chart']["numberSuffix"]="%";
		$result['chart']["valuePosition"]="above";
		$result['styles'] = $this->getLine();
		array_push($response, array('chartResult'=>$result,'chartType'=> 'Line','size'=>1));
		
		 //Top 20 products
		$sqlQuery = "select  count(sku) as count ,title from netotiate.transactions WHERE TRUE";
		$groupBy = ' group by title order by count desc limit 20; ';
		$sqlQuery.= str_replace ('the_date' , 'create_date', $subQuery) . " and  consumer_status = 'purchased' " . $groupBy;
		$ds = $model->executeQuery($sqlQuery);
		$result = $model->transformToSingleSeriesChart($ds, 'title', 'count', _t('ProductArena.product_name'), _t('ProductArena.count'), _t('ProductArena.top_sales'), "");
		array_push($response, array('chartResult'=>$result,'chartType'=> 'Bar2D','size'=>1));
		
		//Sales per category
		$subQuery =  str_replace ('the_date' , 't.create_date', $this->getSubQuery()) ;
		
		$groupBy = ' group by the_date,category_ order by t.create_date asc';
		
		
		$sqlQuery = "select t.affiliate_id, DATE_FORMAT(t.create_date, '%m/%d/%y') as the_date, IF(t.category IS null or t.category = '' or t.category = 'null' , 'N/A',  t.category) as category_
						,count(t.purchase_amount) as sales , round(sum(t.purchase_amount/100*if(af.currency_rate=0,1,af.currency_rate)),2) as revenue
							from netotiate.transactions t , netotiate.affiliates af WHERE TRUE";
		$sqlQuery.= $subQuery . " and t.consumer_status = 'purchased' and t.affiliate_id = af.id " . $groupBy;
		
		$final_sql = 'select the_date , IF(ac.category_name IS NULL , r.category_ , ac.category_name) as category_  , 
				sales,revenue from (' . $sqlQuery . ') as r left join affiliates_categories ac on r.affiliate_id = ac.affiliate_id and r.category_ = ac.category_id order by the_date asc;'; 
		
		
		$ds = $model->executeQuery($final_sql);		
		$ds = $this->fixDateRange($ds,$this->filters['fromDate'],$this->filters['toDate'],array('revenue'=>0,'category_'=>'N/A'));
		$ds = $this->localizeDate($ds, 'the_date');
		$result = $model->transformSeriesToChart($ds, null, 'revenue', 'the_date' , 'category_',_t("ProductArena.revenue_per_category"), _t("ProductArena.date"),_t("ProductArena.revenue"),$this->_currency());
		
		$result['chart']["labelDisplay"]="ROTATE";
		$result['chart']["slantLabels"]="1";
		$result['chart']["showsum"]="1";
		$result['chart']['plotGradientColor'] = '';
		$result['chart']['decimals'] = '0';
		$result['chart']['palette'] = '3';
		$result['chart']['paletteColors'] = '';
		array_push($response, array('chartResult'=>$result,'chartType'=> 'MSColumn2D','size'=>1));
		
		//Sales and Revenue per category
		$groupBy = ' group by category_ order by t.category asc';
		
		if($this->admin)
		{
			$subQuery = str_replace ('the_date' , 't.create_date', $subQuery) . " and  t.consumer_status = 'purchased' and af.id = t.affiliate_id" . $groupBy;
			$sqlQuery = "select t.affiliate_id, IF(t.category IS null or t.category = '' or t.category = 'null' , 'N/A',  t.category) as category_ ,count(t.purchase_amount) as sales , round(sum(t.purchase_amount/100*if(af.currency_rate=0,1,af.currency_rate)),2) as revenue
				 from netotiate.transactions t , netotiate.affiliates af WHERE TRUE";
			
		
		}
		else{
			
			$sqlQuery = "select t.affiliate_id , IF(t.category IS null or t.category = '' or t.category = 'null' , 'N/A',  t.category) as category_ ,count(t.purchase_amount) as sales ,  round(sum(t.purchase_amount/100),2) as revenue 
			from netotiate.transactions t WHERE TRUE";
			$subQuery = str_replace ('the_date' , 't.create_date', $subQuery) . " and  t.consumer_status = 'purchased' " . $groupBy;
			
			
		}
		
		$sqlQuery.= $subQuery;	

		$final_sql = 'select  IF(ac.category_name IS NULL , r.category_ , ac.category_name) as category_  ,
				sales,revenue from (' . $sqlQuery . ') as r left join affiliates_categories ac on r.affiliate_id = ac.affiliate_id and r.category_ = ac.category_id group by category_ order by category_ asc;';
		$ds = $model->executeQuery($final_sql);
		
		
		$totalSales = 0;
		$totalRevenue = 0;
		foreach ($ds as $row)
		{
			$totalSales+= $row['sales'];
			$totalRevenue+= $row['revenue'];			
		}
		
		$result = $model->transformToSingleSeriesChart($ds, 'category_', 'sales', _t('ProductArena.category'), _t('ProductArena.sales'), _t('ProductArena.sales_per_category'), "");
		$result['chart']['subcaption']= _t('ProductArena.total_of').' '._d($totalSales,0);
		if(sizeof($result) >1)
		{
			$result['chart']["showvalues"]="1";
			$result['chart']["showPercentValues"]="1";
			$result['chart']['paletteColors'] = '';
			$this->slice($result['data']);	
		}
		array_push($response, array('chartResult'=>$result,'chartType'=> 'Pie2D','size'=>1));
		
		
		$result = $model->transformToSingleSeriesChart($ds, 'category_', 'revenue', _t('ProductArena.category'), _t('ProductArena.revenue'), _t('ProductArena.revenue_per_category'), $this->_currency());	
		$result['chart']['subcaption']= _t('ProductArena.total_of').' ' . $this->_currency(). _d($totalRevenue,0);
		if(sizeof($result) >1)
		{
			$result['chart']["showvalues"]="1";
			$result['chart']["showPercentValues"]="1";
			$result['chart']['paletteColors'] = '';
			$this->slice($result['data']);
		}
		
		array_push($response, array('chartResult'=>$result,'chartType'=> 'Pie2D','size'=>1));
		return $response;
	}
	
	function getNumberOfDaysInHistory(){
		//number of days in history
		$subQuery = ' ';
		if($this->filters['affiliateId'] != '-1')
			$subQuery.=' and affiliate_id = ' . $this->filters['affiliateId'];
		$sqlQuery =  sprintf("select count(t.id) as count from (select id from dwh.daily_funnel WHERE date(the_date) >= date(date_sub(CURDATE(), interval %d day)) 
			  		 %s group by DATE_FORMAT(the_date,'%s')) as t;", date("t"),  $subQuery, "%d-%m");
		
		$model = new ReportsModel();
		$dsHistory = $model->executeQuery($sqlQuery);
		if(sizeof($dsHistory) > 0)
			$count = $dsHistory[0]['count'];
		else $count=1;
		 
		return $count;
	}
	
	function getCurrentMonthProjection(){
		$subQuery = ' ';
		
		if($this->filters['affiliateId'] != '-1')
			$subQuery.=' and affiliate_id = ' . $this->filters['affiliateId'];
			
		
		//calculate projection
		$sqlQuery = sprintf("SELECT sum(visitors) as visitors , sum(purchases+canceled) as sales , sum((purchase_sum+cancel_sum)*$this->currency_rate) as revenue,
						    round((((sum(original_sum) - sum(purchase_sum))/IF(sum(original_sum) > 0 ,
							sum(original_sum), 1))*100),2) as discount , sum(submitted) as offers
					 FROM dwh.daily_funnel WHERE date(the_date) > date(date_sub(CURDATE(), interval %d day))  ", date("t")+1 );
		$sqlQuery.= $subQuery;
		$model = new ReportsModel();
		$dsProjection = $model->executeQuery($sqlQuery);
		if(sizeof($dsProjection) > 0)
		{
			$count = $this->getNumberOfDaysInHistory();
			if(!isset($count) || $count == 0) $count = 1; 
			$m_days = date("t");
			$days = date("j");
			$days_left = $m_days - $days + 1;			
			$p = $dsProjection[0];
			
			$result = array(
								round($dsProjection[0]['visitors']/$count * $days_left,0),
								round($dsProjection[0]['sales']/$count * $days_left,0),
								round($dsProjection[0]['revenue']/$count * $days_left,0),
								$dsProjection[0]['discount'],
								round($dsProjection[0]['offers']/$count * $days_left,0)
					);
			
		}
		else
			$result = array(0,0,0,0,0);
		
		return $result;
	}
	
	public function getAllUpSummary(){
		$affiliatesQuery = $this->filters['affiliateId'] != -1 ? 'and TRUE and' : " AND dcfs.affiliate_id in (select id from netotiate.affiliates where status = 'activated') and";
		$this->currency_rate = $this->currency_rate == 'currency_rate' ? 'dcfs.currency_rate' : $this->currency_rate;
		
		$sqlQuery = <<<QUERY
			SELECT 	a.name as affiliate,
			        a.id as id,
					sum(dcfs.unique_visitors) as visitors ,
					sum(dcfs.purchases) as sales ,
					sum(dcfs.netotiate_revenue_sum * $this->currency_rate) as revenue,
					round((((sum(dcfs.original_price_sum) - sum(dcfs.netotiate_revenue_sum))/IF(sum(dcfs.original_price_sum) > 0 , sum(dcfs.original_price_sum), 1))*100),2) as discount ,
					sum(dcfs.submitted_offers) as offers,
					sum(dcfs.netotiate_fee_sum*$this->currency_rate) as commission
			FROM dwh.daily_category_funnel dcfs, affiliates a
			WHERE TRUE {$this->getSubQuery()} {$affiliatesQuery}
					a.id = dcfs.affiliate_id
        			group by affiliate_id
					order by a.name;
QUERY;
		
		$model = new ReportsModel();
		$ds = $model->executeQuery($sqlQuery);
		
		$visitorsModel = new UniqueVisitors();
		$uniqueVisitors = $visitorsModel->getAll($this->filters);
		
		$result = array();
		
		foreach ($ds as $index => $affiliateData){
			$affiliate = array();
			if(sizeof($ds) > 0){
				array_push($affiliate , array("title" => $affiliateData['affiliate'], "num" => "", "projection" => ""));
				array_push($affiliate , array("title"=> "Total unique visitors" , "num" => _d($uniqueVisitors[$affiliateData["id"]],0) , "projection"=>""));
				array_push($affiliate , array("title"=>"Offer Submitted" , "num" => _d($affiliateData['offers'],0)  , "projection"=>"" ));
				array_push($affiliate , array("title"=>"Sales" , "num" => _d($affiliateData['sales'],0)  , "projection"=>"" ));
				array_push($affiliate , array("title"=>"Revenue generated" , "num" => $this->_currency().' '. _d($affiliateData['revenue'],0)  , "projection"=>"" ));
				array_push($affiliate , array("title"=>"Average Discount" , "num" => $affiliateData['discount'] > 0 ?  _d($affiliateData['discount']).' %' : 'N/A'  , "projection"=>""));
				if($this->admin)
					array_push($affiliate , array("title"=>"Personali commission" , "num" => $affiliateData['commission'] > 0 ?  $this->_currency().' '. _d($affiliateData['commission'], 2) : 'N/A'  , "projection"=>""));
			}
				
			$result[] = $affiliate;
		}
		
		return $result;
	}
	public function getMonthlySummary(){
		$categoryFilter = ' TRUE ';
		
		$this->currency_rate = $this->currency_rate == 'currency_rate' ? 'dcfs.currency_rate' : $this->currency_rate;
		
		$subQuery = $this->getSubQuery();
		$select = 'a.name as affiliate,';
		
		if($this->filters['affiliateId'] == -1){
			$select = '';
		}
		
		$sqlQuery = <<<QUERY
			SELECT  {$select}
					sum(dcfs.unique_visitors) as visitors , 
                    sum(dcfs.purchases) as sales , 
                    sum(dcfs.netotiate_revenue_sum * $this->currency_rate) as revenue,
                    round((((sum(dcfs.original_price_sum) - sum(dcfs.netotiate_revenue_sum))/IF(sum(dcfs.original_price_sum) > 0 , sum(dcfs.original_price_sum), 1))*100),2) as discount , 
                    sum(submitted_offers) as offers,
                    sum(dcfs.netotiate_fee_sum*$this->currency_rate) as commission
            FROM dwh.daily_category_funnel dcfs, netotiate.affiliates a
            WHERE 	{$categoryFilter} {$subQuery}
					and a.id = dcfs.affiliate_id
QUERY;
		
		$model = new ReportsModel();
		$ds = $model->executeQuery($sqlQuery);
		
		$visitorsModel = new UniqueVisitors();
		$visitors = $visitorsModel->getByAffiliateId($this->filters);
		
		$result = array();
		
		foreach ($ds as $index => $affiliateData){
			if(sizeof($ds) > 0){
				$id = $this->filters['affiliateId'];
				
				if($this->admin){
					if($this->filters['affiliateId'] == '-1'){
						array_push($result , array("title" => "", "num" => "All", "projection" => ""));
					}else{
						array_push($result , array("title" => "", "num" => $affiliateData["affiliate"], "projection" => ""));
					}
				}
				
				array_push($result , array("title"=> "Total unique visitors" , "num" => _d($visitors,0) , "projection"=>""));
				array_push($result , array("title"=>"Offer Submitted" , "num" => _d($affiliateData['offers'],0)  , "projection"=>"" ));
				array_push($result , array("title"=>"Sales" , "num" => _d($affiliateData['sales'],0)  , "projection"=>"" ));
				array_push($result , array("title"=>"Revenue generated" , "num" => $this->_currency().' '. _d($affiliateData['revenue'],0)  , "projection"=>"" ));
				array_push($result , array("title"=>"Average Discount" , "num" => $affiliateData['discount'] > 0 ?  _d($affiliateData['discount']).' %' : 'N/A'  , "projection"=>""));
				if($this->admin)
					array_push($result , array("title"=>"Personali commission" , "num" => $affiliateData['commission'] > 0 ?  $this->_currency().' '. _d($affiliateData['commission'], 2) : 'N/A'  , "projection"=>""));
			}
		}
		
		return $result;
	}
	
	public function getCurrentMonthlySummary(){
		//Current counters for the current month
		$start_date = date('Y'). '-'. date('m'). '-01';
		$end_date = date('Y'). '-'. date('m'). '-'. date('d'). ' 23:59:59';
		
		//Current counters for the current month
		$sqlQuery = <<<QUERY
					SELECT 	sum(visitors) as visitors,
							sum(purchases) as sales, 
							sum(purchase_sum * $this->currency_rate) as revenue,
						    round((((sum(original_sum) - sum(purchase_sum))/IF(sum(original_sum) > 0 ,sum(original_sum), 1))*100),2) as discount, 
						    sum(submitted) as offers
					FROM dwh.daily_funnel 
					WHERE 	the_date >= '$start_date' and 
							the_date <= '$end_date'
QUERY;
		
		$subQuery = ' ';
	
		if($this->filters['affiliateId'] != '-1')
			$subQuery.=' and affiliate_id = ' . $this->filters['affiliateId'];
		
		$sqlQuery.= $subQuery;
		$model = new ReportsModel();
		$ds = $model->executeQuery($sqlQuery);
	
		list( $visitors_p, $sales_p,  $revenue_p, $discount_p, $offers_p ) = $this->getCurrentMonthProjection();
	
		$result = array();
		if(sizeof($ds) > 0)
		{
			$visitorsProjection = $ds[0]['visitors'] + $visitors_p;
			$offersProjection = $ds[0]['offers'] + $offers_p;
			$salesProjection = $ds[0]['sales'] + $sales_p;
			$revenueProjection = $ds[0]['revenue'] + $revenue_p;
			$discountProjection = $discount_p;
	
			array_push($result , array("title"=> "" , "num" => "All" , "projection"=>  ""));
			array_push($result , array("title"=>_t("ProductArena.total_unique_visitors") , "num" => _d($ds[0]['visitors'],0) , "projection"=>  _d($visitorsProjection,0)));
			array_push($result , array("title"=>_t("ProductArena.offer_submitted") , "num" => _d($ds[0]['offers'],0) , "projection"=>_d($offersProjection,0) ));
			array_push($result , array("title"=>_t("ProductArena.sales") , "num" => _d($ds[0]['sales'],0) , "projection"=>_d($salesProjection,0) ));
			array_push($result , array("title"=>_t("ProductArena.revenue_generated") , "num" => $this->_currency(). _d($ds[0]['revenue'],0) , "projection"=>$this->_currency()._d($revenueProjection) ));
			array_push($result , array("title"=>_t("ProductArena.average_discount") , "num" => $ds[0]['discount'] > 0 ?  _d($ds[0]['discount']).' %' : 'N/A'  , "projection"=>$discountProjection. ' %' ));
			
		}
	
		return $result;
	}
	
	function toDDMMYY($strDate){
		if(_t('ProductArena.date_short') != 'mmddyy')
		{
			$arr = explode('/', $strDate);
			if(sizeof($arr) == 3)
				return $arr[1]. '/'.$arr[0].'/'.$arr[2];		
		}
		
		return  $strDate;
	}
	
	function localizeDate(&$ds,$date_key)	{
		foreach($ds as &$r){
			$r[$date_key] = $this->toDDMMYY($r[$date_key]);
		}
		
		return $ds;
	}
	
	function slice(&$ds){
		$max_val = 0;
		$max_pos = 0;
		for ( $i = 0; $i < sizeof($ds) ; $i += 1)
		{
			if( isset( $ds[$i]['value']) && ((int)$ds[$i]['value']) > $max_val)
			{
				$max_val = (int)$ds[$i]['value'];
				$max_pos = $i;				
			}			
		}		
		$ds[$max_pos]['isSliced'] = '1';
	}
	
	
	private function getLine(){
		return json_decode('{
    "definition": [
      {
        "type": "Shadow",
        "name": "LineShadow",
        "distance": "9",
        "color": "8F8F8F",
        "alpha": "50",
        "blury": "5"
      }
    ],
    "application": [
      {
        "toobject": "DATAPLOT",
        "styles": "LineShadow"
      }
    ]
  }');
	}
	
	private function getBevel(){
		return json_decode ('{
		"definition": [
		{
			"type": "Bevel",
			"name": "Bevel_0",
			"distance": "3",
			"shadowcolor": "5B005B",
			"shadowalpha": "100",
			"highlightcolor": "800080",
			"highlightalpha": "61",
			"blury": "6",
			"strength": "4",
			"quality": "2"
		}
		],
		"application": [
		{
			"toobject": "DATAPLOT",
			"styles": "Bevel_0"
		}
		]
		}');
	}
	
	
	
	
	public function isAdvancedFiltersOn(){
		$advanced = false;
		
		if(isset($this->filters['category']) && $this->filters['category'] != '-1'){
			$advanced = true;
		}

		if(isset($this->filters['device']) && $this->filters['device'] != null){
			$advanced = true;
		}

		return $advanced;
	}

	/**
		accepts an array, defining what filters to add
		array(affiliate => true, 'device' => true, 'date' => true, 'category' => true)
	*/
	public function getSubQuery($config = array()){
		$defaultConfig = array(	'affiliate' => true, 
								'device' => true, 
								'date' => true, 
								'category' => true);

		$options = array_merge($defaultConfig, $config);
		$subQuery = ' ';

		$affiliateId 	= $this->filters['affiliateId'];
		$fromDate 		= $this->filters['fromDate'];
		$toDate 		= $this->filters['toDate'];
		$category 		= $this->filters['category'];
		$device 		= $this->filters['device'];
		
		if (isset($affiliateId) && isset($fromDate) && isset($toDate) ){
			if($options['date'])
				$subQuery = " AND the_date >= '{$fromDate}' AND the_date <= '{$toDate} 23:59:59' AND affiliate_id NOT in (4, 5) ";
							
			if ($options['affiliate'] && $affiliateId != '-1')
				$subQuery.= " AND affiliate_id = $affiliateId ";
		}

		if( $options['category'] && $category != '-1' ){
			$subQuery.= " AND category = '$category' ";	
		}
		
		if( $options['device'] && $device ){
			$subQuery.= " AND device_type = '$device' ";	
		}

		return $subQuery;
	}
}