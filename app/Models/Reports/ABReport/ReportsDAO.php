<?php
namespace App\Models\Reports\ABReport;

use DB;

class ReportsDAO {
	
	public function getRetailerList()
	{
		$sql = <<<QUERY
			SELECT 	locale, 
					affiliates.name, 
					affiliates.currency_rate, 
					affiliates.id 
			FROM 	netotiate.affiliates , 
					dwh.daily_funnel 
			WHERE	affiliates.status = 'activated' and 
					affiliates.id <> 5 and 
					affiliates.id = daily_funnel.affiliate_id 
			GROUP BY affiliates.id order by name asc;
QUERY;

		return $this->executeQuery($sql);
	}
	
	public function getPlatforms($filters){
		//Consider using the ReportingServices getSubQuery
		try{
			$sql = "SELECT DISTINCT IFNULL(device_type, 'desktopWeb') as device_type
					FROM dwh.daily_category_funnel		
					WHERE affiliate_id={$filters["affiliateId"]} AND 
						the_date >= DATE('{$filters["fromDate"]}') AND 
						the_date <= DATE('{$filters["toDate"]}');";

			return $this->executeQuery($sql);
		}
		catch (Exception $e){
			return null;
		}
	}

	public function getCategories($filters){
		//Consider using the ReportingServices getSubQuery
		try{
			$sql = "SELECT DISTINCT category as id, IFNULL(category_name, category) AS name 
					FROM (dwh.daily_category_funnel LEFT JOIN netotiate.affiliates_categories ON daily_category_funnel.affiliate_id = affiliates_categories.affiliate_id AND 
						daily_category_funnel.category = affiliates_categories.category_id)
					WHERE daily_category_funnel.category <> '' AND 
						daily_category_funnel.affiliate_id={$filters["affiliateId"]} AND 
						the_date>=DATE('{$filters["fromDate"]}') AND 
						the_date <= DATE('{$filters["toDate"]}');";
			return $this->executeQuery($sql);;
		}
		catch (Exception $e){
			return null;
		}
	}
	
	private function getChartHeader($caption, $xCaption, $yCaption, $numberPrefix='',$sNumberPrefix=''){
		return  array( 
				"caption"=>$caption  ,
				 "xaxisname"=> $xCaption,
				 "yaxisname"=> $yCaption ,
				 "showvalues" => "0" ,
				 "showsum" => "1" ,
				 "decimalSeparator"=>_t('ProductArena.dec_point'),
				 "thousandSeparator"=>_t('ProductArena.thousands_sep'),
				//"rotateValues" => "1" ,
				//"valuePosition" => "BELOW",
				//"placeValuesInside" => "1" ,
				"showalternatehgridcolor" => "0" ,
				//"showalternatehgridcolor": "0"
				
				 "numberPrefix"=>$numberPrefix ,
				 "bgColor" => 'ffffff' ,
				 "showBorder"=>"1" ,
				"divlinecolor"=> "999999",
				"canvasbgcolor"=> "FEFEFE",
				"canvasbasecolor"=> "FEFEFE",
				"showcolumnshadow"=> "1",
				 //"bgAlpha"=>'10' , 
				 "formatNumberScale"=>'0',
				// "useroundedges"=> "1",
				 "exportEnabled"=>'0', 
				 "exportAtClient"=>'1',
				 "exportHandler"=>'fcExporter1',
				 "exportShowMenuItem"=>'0',
				 // "palette"=> "3",
				 //"showcolumnshadow"=> "1",
				 "paletteColors"=>"6EC0D6",
				//"paletteColors"=>"0c1428, f6dc1e , ced0d4 , 9ea1a9, 6d727e , 3d4353, 43c7ef, 0ec63d, f42f2f",
				//"paletteColors"=>"a8d0fb",
				"plotGradientColor"=>'6EC0D6',
				 //"paletteColors"=>"0c1428, f6dc1e , ced0d4 , 9ea1a9, 6d727e , 3d4353, 43c7ef, 0ec63d, f42f2f",
				 "linecolor"=> "ea0a0a",
				 //"linealpha" => "85",
				 "lineThickness" => "3",
				 "showShadow" => "1",
				 "anchorRadius" => "4",
				 "anchorBgColor" => "F2F2F2",
				 "anchorBorderThickness" => "3",
				 "sNumberPrefix" => $sNumberPrefix,
				 "enableSmartLabels"=>'1',
				 "forceDecimals"=>'1',
				 "forceYAxisValueDecimals"=>'0',
				 "decimals" => '2',
				 "plotSpacePercent" => '20',
				 "maxLabelWidthPercent"=>'20',
				 "exportFileName" => $caption
				 
				
				 
				); 
	}
	
	// The function transform data to the a multi series chart type.
	// $seriesColumnsList = ["salesColumn" -> "Sales caption"]
	public function transformColumnsSeriesToChart($ds,$xAxis, $yAxis,$categoryName,$seriesColumnsList,$caption,$xCaption,$yCaption,$numberPrefix = '',$sNumberPrefix=''){
		$chart = $this->getChartHeader($caption  , $xCaption,  $yCaption  , $numberPrefix, $sNumberPrefix);
		$categories = array();
		$series = array();
		$dataset = array();		
		$categories_col = array();
		
		foreach (array_values($seriesColumnsList) as $row){
			array_push( $series,  array('seriesname'=> $row,'data'=>array()));
		}
		
		
		foreach ($ds as $row){
			// Get the categories....
			if ( ! array_key_exists($row[$categoryName], $categories_col)){
	
				$categories_col[(string)$row[$categoryName]] =  (string)$row[$categoryName];
				array_push($categories,array('label'=>$row[$categoryName]) );
			}
			
			foreach (array_keys($seriesColumnsList) as $column){
				
				for ( $i = 0; $i < sizeof($series) ; $i += 1) {
 					if( $series[$i]['seriesname'] ==  (string)$seriesColumnsList[$column]){
 						//$prefix = $numberPrefix==''  ? $sNumberPrefix : $numberPrefix;
 						//tooltext' =>  $series[$i]['seriesname'].', '.$prefix .number_format($row[$column])
 						array_push(  $series[$i]['data'], array('value' =>  $row[$column] ));
 					}
				}
			}
		}
	
		$result = array('chart'=>$chart , 'categories' => array( 'category' => $categories) , 'dataset' => $series );
		return $result;
	}
	
	// The function transform data to the a multi series chart type.
	public function transformSeriesToChart($ds,$xAxis, $yAxis,$categoryName,$seriesColumn,$caption,$xCaption,$yCaption,$numberPrefix=''){
		$chart = $this->getChartHeader($caption  , $xCaption,  $yCaption  , $numberPrefix);
		$categories = array();
		$series = array();
		$dataset = array();
		$series_col = array();
		$categories_col = array();
		
		foreach ($ds as $row){
			// Create categories....
			if ( ! array_key_exists($row[$categoryName], $categories_col)){
				    
				$categories_col[(string)$row[$categoryName]] =  (string)$row[$categoryName];
				array_push($categories,array('label'=>$row[$categoryName]) );
			}
			// Create series....
			if ($row[$seriesColumn]!= null && array_key_exists($row[$seriesColumn], $series_col) == false)
			{
				$series_col[(string)$row[$seriesColumn]] =  (string)$row[$seriesColumn];
				array_push( $series,  array('seriesname'=>$row[$seriesColumn],'data'=>array()));				 
			}
		}
		
		
		foreach($categories as $row){
			for ( $i = 0; $i < sizeof($series) ; $i += 1)
				array_push(  $series[$i]['data'], array('value' => null ));
		}

		
		foreach($ds as $row)
		{
			$x = $row[$categoryName];
			$index = $this->getIndex($categories, $x);
			if($index>=0)
			{
				for ( $i = 0; $i < sizeof($series) ; $i += 1)
				{
					if($row[$seriesColumn] == $series[$i]['seriesname'])
					{
						$series[$i]['data'][$index]['value'] =  $row[$yAxis];
					}
				}
			}
		}
		
		
		$result = array('chart'=>$chart , 'categories' => array( 'category' => $categories) , 'dataset' => $series );
		return $result;
	}
	
	function getIndex($arr,$data){
		for ( $i = 0; $i < sizeof($arr) ; $i += 1)
			if((string)$arr[$i]['label'] ==(string)$data)
				return $i;
		
		return null;
	}

	public static function toAssocArray($object)
    {
        if (is_object($object)) {
            $object = get_object_vars($object);
        }
        
        if (is_array($object)) {
            return array_map(function($object){return self::toAssocArray($object);}, $object);
        } else {
            // Return array
            return $object;
        }
    }

    public static function toArray($object)
    {
        if (is_object($object)) {
            $object = array_values(get_object_vars($object));
        }
        
        if (is_array($object)) {
            return array_map(function($object){return self::toArray($object);}, $object);
        } else {
            // Return array
            return $object;
        }
    }
	
	public function executeQuery($sqlQuery){
	/*	\Netotiate_Log::debug( $sqlQuery );*/ //TODO: get back here
		return $this->toAssocArray(DB::select($sqlQuery));
	}
	
	// The function convert dataset into datatable source object. 
	public function transformToTableData( $ds , $columns ){
		$aaData = array();
		
		foreach ($ds as $row){
			$sub_row = array_intersect_key($row, array_flip($columns));
			array_push($aaData,array_values($sub_row) );		
		}

		$json_data = array('aaData'=>$aaData);
		
		return $json_data; 
		
	}
	
	/// The function transform data to the a single series chart type. 
	public function transformToSingleSeriesChart($ds, $xAxisColumn, $yAxisColumn,$xCaption,$yCaption, $caption,$numberPrefix=''){
		//$chart = array("caption"=> $chartCaption , "xaxisname"=>$xAxisCaption, "yaxisname"=> $yAxisCaption ,  "showvalues" => "0" , "exportEnabled"=>'1',  "exportAtClient"=>'1', "exportHandler"=>'fcExporter1');
		$chart = $this->getChartHeader($caption  , $xCaption,  $yCaption  , $numberPrefix);
		$data = array();

		foreach ($ds as $row){
			array_push ( $data,  array( "label"=> $row[$xAxisColumn] , "value" => $row[$yAxisColumn] ) );
		}
		
		$result = array('chart'=>$chart , 'data' => $data );
		return $result; 
	}
}