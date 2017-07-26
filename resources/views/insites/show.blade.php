@extends('layouts.admin')

@section('sidebar-content')
@endsection

@section('custom-styles')
<link href="{{ url('styles/insites.css') }}" rel="stylesheet" type="text/css" media="all"  />
<style>
    div.d3-tooltip {
      position: absolute;
      text-align: center;
      width: 60px;
      height: 28px;
      padding: 2px;
      font: 12px sans-serif;
      line-height:24px;
      background: #000;
      color: #fff;
      border: 0px;
      border-radius: 5px;
      pointer-events: none;
      z-index:9999;
    }
</style>
@endsection

@section('content')


<div class="insites-container">
    <div class="row" style="margin:0px auto; max-width:1280px">
        <h2 style="margin:15px;">Insites</h2>
        <div class="col-md-3 less-row-padding">
            <div class="panel panel-default">
                <div class="panel-body" style="text-align:center;background-color: #ff5522">
                    <div class="insites-loading-container">
                        <div class="insites-loading"></div>
                        <div id="loading-text">loading</div>
                    </div>
                    <div id="score-card-container" style="display:none;">
                        <p style="color:#fff;font-size:1.5em;">Your Overall Score</p>
                        <div id="score-card"></div>
                    </div>
                </div>
            </div>

            <div id="conversion-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/conversion_icon.jpg" /></div>Conversion<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body" style="text-align:center;position:relative;">
                    <p style="color:#b2b2b2;padding-bottom:10px">The percentage of purchases from unique visitors.</p>
                    <div style="position:absolute;clip:rect(0px,290px,155px,0px);left:0;right:0;margin-left:auto;margin-right:auto;"><canvas id="gauge-id"></canvas></div>
                    <div style="margin-top:170px">
                        <p style="color:#6e7177">Below Industry Average</p>
                        <p style="color:#6e7177">Industry Average</p>
                        <p style="color:#6e7177">Above Industry Average</p>
                    </div>
                </div>
                <div class="panel-footer reports-panel-footer"><b>Tip:</b> Drive your users to complete more purchases. <a href="#">Learn How.</a></div>
            </div>

            <div id="arpu-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/ARPU_icon.jpg" /></div>ARPU<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body" style="text-align: center;">
                    <p style="color:#b2b2b2;">Average revenue per unique visitor</p>
                    <div id="figure" style="display:inline-block;margin:10px 0;">
                        <div id="figure-left" style="float:left;margin-right:35px;"></div>
                        <div id="figure-right" style="float:left"></div>
                        <div class="clearfix"></div>
                    </div>
                    <div style="display:inline-block;">
                        <div style="float:left;margin-right: 19px">
                            <div class="circle friend">
                                <p style="color:#6ab414" id="arpu-customer">$0.0</p>
                            </div><br/>
                            Your Avg
                        </div>
                        <div style="float:left;">
                            <div class="circle friend">
                                <p id="arpu-benchmark">$0.0</p>
                            </div><br/>
                            Industry Avg
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="panel-footer reports-panel-footer"><b>Tip:</b> Increase your visitor's spend on the site. <a href="#">Learn How</a></div>
            </div>

            <div id="loyalty-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/Loyalty_icon.jpg" /></div>Loyalty<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body" style="text-align: center;">
                    <p style="color:#b2b2b2;">Percentage of signed in visitors</p>
                    <div id="heart" style="display:inline-block;margin-top:10px">
                        <div id="heart-left" style="float:left;margin-right:1px;"></div>
                        <div id="heart-right" style="float:left"></div>
                    </div>
                    <div  class="clearfix"></div>
                    <div style="padding:20px 0;">
                        <div style="float:left;width:50%;text-align:right;padding:10px;border-right:1px solid #b2b2b2;line-height:1.2em"><p style="font-size:2em;color:#fa3131">23%</p><span style="color:#6e7177">Your Avg</span></div>
                        <div style="float:left;width:50%;text-align:left;padding:10px;line-height:1.2em">
                        <p style="font-size:2em;color:#6e7177">30%</p><span style="color:#6e7177">Industry Avg</span></div>
                    </div>
                </div>
                <div class="panel-footer reports-panel-footer"><b>Tip:</b> Encourage your visitor's loyalty. <br/><a href="#">Learn How</a></div>
            </div>
        </div>

        <div id="sales-funnel-panel" class="col-md-5 less-row-padding">
            <div class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/funnel_icon.jpg" /></div>Sales Funnel<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body" style="position:relative">
                    <div style="background: url(images/insites/Funnel_upper.png) center center no-repeat;height:120px;text-align: center;padding-top:61px;line-height: 1.3em;" />
                        <span style="color:#fff;font-size:1.8em;" id="unique-visitors">0</span><br/>
                        <span style="color:#fff;">Unique visitors</span>
                    </div>
                    <div style="background: url(images/insites/Funnel_middle.png) center center no-repeat;height:150px;text-align: center;padding-top:66px;line-height: 1.3em;" />
                        <span style="color:#fff;font-size:1.8em;" id="unique-added">0</span><br/>
                        <p style="margin:0 auto;color:#fff;width:160px">Unique visitors who added to cart</p>
                    </div>
                    <div style="background: url(images/insites/Funnel_bottom.png) center center no-repeat;height:174px;text-align: center;padding-top:74px;line-height: 1.3em;" />
                        <span style="color:#fff;font-size:1.8em;" id="unique-purchases">0</span><br/>
                        <p style="margin:0 auto;color:#fff;width:120px">Unique visitors who made a purchase</p>
                    </div>
                    <div style="position:absolute;left:0;top:149px;margin-left:14px;">
                        <div style="line-height: 1.3em">
                            <span id="sales-funnel-cart-your-average" style="font-size:1.7em;color:#6ab414">0.00%</span><br />
                            <span style="color:#b2b2b2">Your Avg</span>
                        </div>
                        <div style="margin-top:16px;line-height: 1.3em">
                            <span id="sales-funnel-cart-industry-average" style="font-size:1.7em;color:#6e7177">0.00%</span><br />
                            <span style="color:#b2b2b2">Industry Avg</span>
                        </div>
                        <div style="margin-left:20px;margin-top:5px;"><img src="images/insites/Funnel_lower_indicator.png"/>
                        </div>
                    </div>
                    <div style="position:absolute;right:0;top:18px;margin-right:14px;">
                        <div style="line-height: 1.3em;text-align: right">
                            <span id="sales-funnel-unique-your-average" style="font-size:1.7em;color:#fa2323">0.00%</span><br />
                            <span style="color:#b2b2b2">Your Avg</span>
                        </div>
                        <div style="margin-top:16px;line-height: 1.3em; text-align: right">
                            <span id="sales-funnel-unique-industry-average" style="font-size:1.7em;color:#6e7177">0.00%</span><br />
                            <span style="color:#b2b2b2">Industry Avg</span>
                        </div>
                        <div style="margin-right:20px;margin-top:5px;">
                            <img src="images/insites/Funnel_upper_indicator.png"/>
                        </div>
                    </div>
                </div>
                <div class="panel-footer reports-panel-footer" style="text-align: center;"><b>Tip:</b> You can improve your funnel performance</div>
            </div>
            <div id="average-products-per-order-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/avg_products_icon.jpg" /></div>Average Products Per Order<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body" style="text-align: center">
                    <p style="color:#b2b2b2;text-align:center;padding-bottom:10px">The average amount of unique products per order.</p>
                    <div style="display:inline-block;">
                        <div class="chart-container" style="float:left;width:170px;">
                            <div style="float:left;height:70px;margin-top:5px;">
                                <div class="chart-line" style="color:#6e7177;height:33%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                                <div class="chart-line" style="color:#6e7177;height:33%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                                <div class="chart-line" style="color:#6e7177;height:34%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                            </div>
                            <div id="avg-product-per-order" style="float:right;background-color:#fff ">
                                <div id="avg-product-per-order-left" style="float:left;"></div>
                                <div id="avg-product-per-order-right" style="float:left;"></div>
                            </div>
                        </div>
                        <div style="float:left;margin-left:15px;">
                            <div style="float:left;padding:10px 15px;border-right:1px solid #b2b2b2">
                                <div style="height:35px"><img src="images/insites/your_avg_icon.jpg" height="31" /></div>
                                <span style="font-size:2em;color:#fa3131" id="average-products-per-order-customer">0.00</span><br/><span style="color:#6e7177">Your Avg</span>
                            </div>
                            <div style="float:left;padding:10px 15px">
                                <div style="height:35px"><img src="images/insites/industry_avg_icon.jpg" height="31" /></div>
                                <span style="font-size:2em;color:#6e7177" id="average-products-per-order-benchmark">0.00</span><br/><span style="color:#6e7177">Industry Avg</span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="panel-footer reports-panel-footer" style="text-align: center;"><b>Tip:</b> Increase your average number of products per order. <a href="#">Learn How</a></div>
            </div>
        </div>

        <div class="col-md-4 less-row-padding right-column">
            <div id="discount-ratio-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/discount_ratio_icon.jpg" /></div>Discount Ratio<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body">
                    <p style="color:#b2b2b2;text-align:center;padding-bottom:10px">Average discount rate given to buyers</p>
                    <div style="min-width:260px">
                        <div class="chart-container" style="float:left;height:208px;width:150px;">
                            <div id="price-tag-container" style="float:right; display:inline-block;width:120px;border-left:10px solid white">
                                <div id="price-tag-left" style="float:left;"></div>
                                <div id="price-tag-right" style="float:left;"></div>
                            </div>
                            <div style="margin-top:53px;height:170px;">
                                <div class="chart-line" style="color:#6e7177;height:20%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                                <div class="chart-line" style="color:#6e7177;height:20%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em;"></div>
                                <div class="chart-line" style="color:#6e7177;height:20%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em;"></div>
                                <div class="chart-line" style="color:#6e7177;height:20%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em;"></div>
                                <div class="chart-line" style="color:#6e7177;height:20%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em;"></div>
                            </div>

                        </div>
                        <div style="float:left;margin-left:15px">
                            <div><span id="discount-ratio-your-average" style="font-size:1.7em;color:#6ab414">0.00%</span><p style="color:#6e7177">Your Avg<br/>discount</p><p><span style="color:#6e7177">Which sums<br/> up to </span><span style="color:#6ab414">0.0M</span></p></div>
                            <div><span id="discount-ratio-industry-average" style="font-size:1.7em;">0.0%</span><p style="color:#6e7177"><span>Industry Avg<br/>Discount</span></p></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                </div>
                <div class="panel-footer reports-panel-footer"><b>Tip:</b> Save margins by giving lower discounts while keeping your sale volume</div>
            </div>
            <div id="discount-volume-rate-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/discount_volume_rate_icon.jpg" /></div>Discount Volume Rate<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body">
                    <p style="color:#b2b2b2;text-align:center;padding-bottom:10px">Percentage of discounted orders</p>
                    <div style="min-width:290px">
                        <div class="chart-container" style="height:178px;width:140px;float:left">
                            <div id="discount-volume-rate-chart" style="float:right;"></div>
                            <div class="chart-line" style="color:#6e7177;height:50%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                            <div class="chart-line" style="color:#6e7177;height:50%;border-top:1px solid #b2b2b2;border-bottom:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                        </div>
                        <div style="float:left;margin-left:15px;">
                            <div><span id="discount-volume-rate-your-average" style="font-size:1.7em;color:#6ab414"></span><br/><span style="color:#6e7177">Your Avgerage</span></div>
                            <div style="margin-top:20px;"><span id="discount-volume-rate-industry-average" style="font-size:1.7em;color:#6e7177"></span><br/><span style="color:#6e7177">Industry Average</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="average-order-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading"><div class="pull-left" style="margin-right:10px;"><img src="images/insites/AOV_icon.jpg" /></div>AOV<div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div></div>
                <div class="panel-body">
                    <p style="color:#b2b2b2;text-align:center;padding-bottom:10px">Average order value</p>
                    <div style="min-width:290px">
                        <div style="height:178px;width:140px;float:left">
                            <div id="aov-chart" style="float:right;"></div>
                            <div class="chart-line" style="color:#6e7177;height:33%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                            <div class="chart-line" style="color:#6e7177;height:33%;border-top:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                            <div class="chart-line" style="color:#6e7177;height:34%;border-top:1px solid #b2b2b2;border-bottom:1px solid #b2b2b2;text-align:left;font-size:0.8em"></div>
                        </div>
                        <div style="float:left;margin-left:15px;">
                                <div><span id="aov-customer" style="font-size:1.7em;color:#6ab414">$0</span><br/><span style="color:#6e7177">Your Average</span></div>
                                <div style="margin-top:20px"><span id="aov-benchmark" style="font-size:1.7em;color:#6e7177;">$0</span><br/><span style="color:#6e7177">Industry Average</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="shopping-abandonment-panel" class="panel panel-default">
                <div class="panel-heading reports-panel-heading">
                    <div class="pull-left" style="margin-right:10px;"><img src="images/insites/abandonment_icon.jpg" /></div>
                    <div class="pull-left">Shopping <br />Abandonment Rate</div>
                    <div class="pull-right"><a href="#" data-toggle="tooltip" title="Hooray!"><img src="images/insites/info_icon.jpg" /></a></div>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                     <p style="color:#b2b2b2;text-align:center;padding-bottom:10px">Percentage of carts that didn't <br/>end up as orders.</p>
                    <div style="float:left;margin-right:20px;">
                        <p style="background-position:right 6%;background-image:url('images/insites/cart_avg_icon.jpg');background-repeat: no-repeat; width:80px;line-height:1.2em;padding-right:6px;color:#6e7177"><span style="font-size:1.8em;color:#fa3131">85%</span><br/>Your Avg</p>
                        <p style="background-position:right 6%;background-image:url('images/insites/cart_avg_icon.jpg');color:#6e7177;background-repeat: no-repeat; width:80px;line-height:1.2em;padding-right:6px;margin-top:30px"><span style="font-size:1.8em">75%</span><br/>Industry Avg</p>
                    </div>
                    <div style="float:left;">
                        <div style="float:right; width:173px;height:130px;">
                           <div id="" style="float:left;width:140px;background-color:#fff">
                                <div id="shopping-abandonment-chart-left" style="float:left;"></div>
                                <div id="shopping-abandonment-chart-right" style="float:left;"></div>
                                <div class="clearfix"></div>
                            </div>
                            <div style="color:#6e7177;height:25%;border-top:1px solid #b2b2b2;text-align:right;font-size:0.8em">100%</div>
                            <div style="color:#6e7177;height:25%;border-top:1px solid #b2b2b2;text-align:right;font-size:0.8em;">75%</div>
                            <div style="color:#6e7177;height:25%;border-top:1px solid #b2b2b2;text-align:right;font-size:0.8em;">50%</div>
                            <div style="color:#6e7177;height:25%;border-top:1px solid #b2b2b2;text-align:right;font-size:0.8em;">25%</div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer reports-panel-footer"><b>Tip:</b> You can reduce your cart abandonment rate. <a href="#">Learn How</a></div>
            </div>
        </div>
    </div>
  </div>
  <div id="vizContainer" style="display:none;"></div>
@endsection

@section('custom-javascript-top')
    <script src="scripts/async.1.5.2.min.js"></script>
    <script src="{{ $tableau_host_url }}/javascripts/api/tableau-2.min.js"></script>
    <script src="scripts/in-viewport.min.js"></script>
    <script src="scripts/numeral.js"></script>
    <script>
        var viz, sheet, table;

        function initViz() {
            var containerDiv = document.getElementById("vizContainer"),
                url = "{{ $report_url }}",
                options = {
                    hideTabs: true,
                    hideToolbar: true,
                    onFirstInteractive: function () {
                        async.parallel({
                            'customers': function(callback) {
                                getUnderlyingData('Customers', function(err, niceData) {
                                    callback(err, niceData);
                                });
                            },
                            'benchmarks': function(callback) {
                                getUnderlyingData('Benckmarks', function(err, niceData) {
                                    callback(err, niceData);
                                });
                            }
                        }, function(err, results) {
                            onGotVizData(results);
                        });
                    }
                };
            viz = new tableau.Viz(containerDiv, url, options);
        }

        function getUnderlyingData(sheetName, cb){
            //convert to field:values convention
            var reduceToObjects = function(cols, data) {
              var fieldNameMap = $.map(cols, function(col) { return col.$0.$1; });
              var dataToReturn = $.map(data, function(d) {
                return d.reduce(function(memo, value, idx) {
                  memo[fieldNameMap[idx]] = value.formattedValue; return memo;
                }, {});
              });
              return dataToReturn;
            }

            /*
            viz.getWorkbook().getActiveSheet().getWorksheets().forEach(
                function(sheet) {
                    console.log(sheet.getName())
                }
            );
            */

            sheet = viz.getWorkbook().getActiveSheet().getWorksheets().get(sheetName);

            options = {
                maxRows: 0, // Max rows to return. Use 0 to return all rows
                ignoreAliases: false,
                ignoreSelection: true,
                includeAllColumns: false
            };

            sheet.getUnderlyingDataAsync(options).then(function(t){
                table = t;
                var columns = table.getColumns();
                var data = table.getData();
                var niceData = reduceToObjects(columns, data);
                return cb(null, niceData);
            });
        }

        $(document).ready(function(){
            $("#menu-toggle").trigger("click");
            $('[data-toggle="tooltip"]').tooltip();

            initViz();
        });

    </script>
@endsection


@section('custom-javascript')
    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script src="scripts/gauge.min.js"></script>
    <script src="scripts/d3.bundle.js?v=1.2"></script>
    <script>
        function onGotVizData(data) {
            var customerData = $(data['customers']).filter(function(idx){
                return ((data['customers'][idx]["Auth Key"] == '{{$auth_key}}') &&
                    (data['customers'][idx]["Month"] == '5'))
            });

            $('.insites-loading-container').hide();
            $('#score-card-container').show();


            // console.log(data);
            // console.log(customerData);

            inViewport(document.getElementById('loyalty-panel'), function() {
                animateLoyalty(data, customerData);
            });

            inViewport(document.getElementById('shopping-abandonment-panel'), function() {
                animateShoppingAbandonment(data, customerData);
            });

            // SCORE
            scoreCard.animate();

            // ARPU
            var arpuCustomer = customerData[0]["Arpu"].replace(",","") / 100 / 100;
            var arpuBenchmark = data['benchmarks'][0]["Arpu Benchmark"].replace(",","");

            if (arpuCustomer < arpuBenchmark) {
                var arpuCustomerPercent = Math.abs(((arpuCustomer/arpuBenchmark).toFixed(2) * 100));
                var arpuBenchmarkPercent = 100;
            } else {
                var arpuCustomerPercent = 100;
                var arpuBenchmarkPercent = Math.abs(((arpuBenchmark/arpuCustomer).toFixed(2) * 100));
            }

            figureLeft.percent = arpuCustomerPercent;
            figureLeft.animate();

            figureRight.percent = arpuBenchmarkPercent;
            figureRight.animate();

            d3.countUp("p#arpu-customer").properties({
                format: "$,.2",
                start: 0,
                end: arpuCustomer,
                duration: 1000
            });

            d3.countUp("p#arpu-benchmark").properties({
                format: "$,.2",
                start: 0,
                end: arpuBenchmark,
                duration: 1000
            });

            // AVERAGE PRODUCTS PER ORDER
            var appoCustomer = customerData[0]["Avg Products Per Order"].replace(",","");
            var appoBenchmark = data['benchmarks'][0]["Avg Products Per Order Benchmark"].replace(",","");

            fillChartLines('#average-products-per-order-panel .chart-line', Math.max(appoCustomer,appoBenchmark));

            if (appoCustomer < appoBenchmark) {
                var appoCustomerPercent = Math.abs(((appoCustomer/appoBenchmark).toFixed(2) * 100)) - 10; // there is an offset
                var appoBenchmarkPercent = 100;
            } else {
                var appoCustomerPercent = 100;
                var appoBenchmarkPercent = Math.abs(((appoBenchmark/appoCustomer).toFixed(2) * 100));
            }

            avgProductPerOrderLeft.percent = appoCustomerPercent;
            avgProductPerOrderLeft.animate();

            avgProductPerOrderRight.percent = appoBenchmarkPercent;
            avgProductPerOrderRight.animate();

            d3.countUp("span#average-products-per-order-customer").properties({
                format: ",.3n",
                start: 0,
                end: appoCustomer,
                duration: 1000
            });

            d3.countUp("span#average-products-per-order-benchmark").properties({
                format: ",.3n",
                start: 0,
                end: appoBenchmark,
                duration: 1000
            });

            // SALES FUNNEL
            var funnelPctStepOneBenchmark = data['benchmarks'][0]["Funnel Pct Step One Benchmark"];
            var funnelPctStepTwoBenchmark = data['benchmarks'][0]["Funnel Pct Step Two Benchmark"];

            var funnelPctStepOneCustomer = customerData[0]["Funnel Pct Step One"];
            var funnelPctStepTwoCustomer = customerData[0]["Funnel Pct Step Two"];

            d3.countUp("span#sales-funnel-unique-your-average").properties({
                format: ",.2%",
                start: 0.01,
                end: funnelPctStepOneCustomer / 100,
                duration: 1000
            });

            d3.countUp("span#sales-funnel-unique-industry-average").properties({
                format: ",.2%",
                start: 0.01,
                end: funnelPctStepOneBenchmark / 100,
                duration: 1000
            });

            d3.countUp("span#sales-funnel-cart-your-average").properties({
                format: ",.2%",
                start: 0.01,
                end: funnelPctStepTwoCustomer / 100,
                duration: 1000
            });

            d3.countUp("span#sales-funnel-cart-industry-average").properties({
                format: ",.2%",
                start: 0.01,
                end: funnelPctStepTwoBenchmark / 100,
                duration: 1000
            });

            d3.countUp("span#unique-visitors").properties({
                format: ",d",
                start: 0,
                end: customerData[0]["Funnel Uv"],
                duration: 900
            });

            d3.countUp("span#unique-added").properties({
                format: ",d",
                start: 0,
                end: customerData[0]["Funnel Uv Added To Cart"],
                duration: 800
            });

            d3.countUp("span#unique-purchases").properties({
                format: ",d",
                start: 0,
                end: customerData[0]["Funnel Uv Purchased"],
                duration: 700
            });

            // DISCOUNT RATIO
            var discountRatioCustomer = customerData[0]["Discount Ratio"];
            var discountRatioBenchmark = data['benchmarks'][0]["Discount Ratio Benchmark"];

            fillChartLines('#discount-ratio-panel .chart-line', Math.max(discountRatioCustomer,discountRatioBenchmark)/100, '0%', 1);

            if (discountRatioCustomer < discountRatioBenchmark) {
                var discountRatioCustomerPercent = Math.abs(((discountRatioCustomer/discountRatioBenchmark).toFixed(2) * 100));
                var discountRatioBenchmarkPercent = 100;
            } else {
                var discountRatioCustomerPercent = 100;
                var discountRatioBenchmarkPercent = Math.abs(((discountRatioBenchmark/discountRatioCustomer).toFixed(2) * 100));
            }

            priceTagLeft.percent = discountRatioCustomerPercent;
            priceTagLeft.animate();

            priceTagRight.percent = discountRatioBenchmarkPercent;
            priceTagRight.animate();

            d3.countUp("span#discount-ratio-your-average").properties({
                format: ",.2%",
                start: 0.00,
                end: discountRatioCustomer / 100,
                duration: 1000
            });

            d3.countUp("span#discount-ratio-industry-average").properties({
                format: ",.2%",
                start: 0.01,
                end: discountRatioBenchmark/100,
                duration: 1000
            });

            // AOV
            var aovCustomer = customerData[0]["Aov"].replace(",","") / 100;
            var aovBenchmark = data['benchmarks'][0]["Aov Benchmark"].replace(",","");

            fillChartLines('#average-order-panel .chart-line', Math.max(aovCustomer, aovBenchmark), '$0', 1);

            d3.barGraph('#aov-chart').properties({
                'height': 178,
                'width': 110,
                'colors': ['#16315c', '#585c63'],
                'data': [aovCustomer, aovBenchmark],
                'duration': 1200
            });

            d3.countUp("span#aov-customer").properties({
                format: "$,d",
                start: 0.00,
                end: Math.round(aovCustomer),
                duration: 700
            });

            d3.countUp("span#aov-benchmark").properties({
                format: "$,d",
                start: 0.00,
                end: aovBenchmark,
                duration: 700
            });

            // DISCOUNT VOLUME RATE
            var discountVRateCustomer = customerData[0]["Discount Volume Ratio"].replace(",", "");
            var discountVRateBenchmark = data['benchmarks'][0]["Discount Volume Ratio Benchmark"].replace(",", "");

            fillChartLines('#discount-volume-rate-panel .chart-line', Math.max(discountVRateCustomer * 100, discountVRateBenchmark)/100, '0%', 1);

            d3.barGraph('#discount-volume-rate-chart').properties({
                'height': 178,
                'width': 110,
                'colors': ['#585c63', '#16315c'],
                'data': [discountVRateCustomer * 100, discountVRateBenchmark],
                'duration': 1200
            });

            d3.countUp("span#discount-volume-rate-your-average").properties({
                format: ",.0%",
                start: 0.00,
                end: discountVRateCustomer,
                duration: 700
            });

            d3.countUp("span#discount-volume-rate-industry-average").properties({
                format: ",.0%",
                start: 0.00,
                end: discountVRateBenchmark / 100,
                duration: 700
            });

            // CONVERSION
            gauge.value = 2
        }

        var figureRight = d3.iconGraph('#figure-right');
        figureRight.emptyGraph = 'images/insites/ARPUU_empty_new.jpg';
        figureRight.fullGraph = 'images/insites/ARPUU_full_grey_new.jpg';
        figureRight.width = '59px';
        figureRight.height = '123px';
        figureRight.draw();

        var figureLeft = d3.iconGraph('#figure-left');
        figureLeft.emptyGraph = 'images/insites/ARPUU_empty_new.jpg';
        figureLeft.fullGraph = 'images/insites/ARPUU_full_blue_new.jpg';
        figureLeft.width = '59px';
        figureLeft.height = '123px';
        figureLeft.draw();

        var priceTagLeft = d3.iconGraph('#price-tag-left');
        priceTagLeft.emptyGraph = 'images/insites/discount_ratio_empty_left.jpg';
        priceTagLeft.fullGraph = 'images/insites/discount_ratio_full_left.jpg';
        priceTagLeft.width = '55px';
        priceTagLeft.height = '208px';
        priceTagLeft.draw();
        priceTagLeft.duration = 1200;

        var priceTagRight = d3.iconGraph('#price-tag-right');
        priceTagRight.emptyGraph = 'images/insites/discount_ratio_empty_right.jpg';
        priceTagRight.fullGraph = 'images/insites/discount_ratio_full_right.jpg';
        priceTagRight.width = '54px';
        priceTagRight.height = '208px';
        priceTagRight.draw();
        priceTagRight.duration = 1600;

        var avgProductPerOrderLeft = d3.iconGraph('#avg-product-per-order-left');
        avgProductPerOrderLeft.emptyGraph = 'images/insites/average_products_per_order_empty_left.jpg';
        avgProductPerOrderLeft.fullGraph = 'images/insites/average_products_left_full.jpg';
        avgProductPerOrderLeft.width = '43px';
        avgProductPerOrderLeft.height = '106px';
        avgProductPerOrderLeft.draw();

        var avgProductPerOrderRight = d3.iconGraph('#avg-product-per-order-right');
        avgProductPerOrderRight.emptyGraph = 'images/insites/average_products_per_order_empty_right.jpg';
        avgProductPerOrderRight.fullGraph = 'images/insites/average_products_right_full.jpg';
        avgProductPerOrderRight.width = '98px';
        avgProductPerOrderRight.height = '106px';
        avgProductPerOrderRight.draw();

        var heartLeft = d3.iconGraph('#heart-left');
        heartLeft.emptyGraph = 'images/insites/LeftHeartEmpty.png';
        heartLeft.fullGraph = 'images/insites/LeftHeartFull.png';
        heartLeft.width = '52px';
        heartLeft.height = '95px';
        heartLeft.draw();

        var heartRight = d3.iconGraph('#heart-right');
        heartRight.emptyGraph = 'images/insites/RightHeartEmpty.png';
        heartRight.fullGraph = 'images/insites/RightHeartFull.png';
        heartRight.width = '52px';
        heartRight.height = '95px';
        heartRight.draw();

        var shoppingAbanLeft = d3.iconGraph('#shopping-abandonment-chart-left');
        shoppingAbanLeft.emptyGraph = 'images/insites/shopping_abandonment_empty_left.jpg';
        shoppingAbanLeft.fullGraph = 'images/insites/shopping_abandonment_full_left.jpg';
        shoppingAbanLeft.width = '102px';
        shoppingAbanLeft.height = '122px';
        shoppingAbanLeft.draw();

        var shoppingAbanRight = d3.iconGraph('#shopping-abandonment-chart-right');
        shoppingAbanRight.emptyGraph = 'images/insites/shopping_abandonment_empty_right.jpg';
        shoppingAbanRight.fullGraph = 'images/insites/shopping_abandonment_full_right.jpg';
        shoppingAbanRight.width = '33px';
        shoppingAbanRight.height = '122px';
        shoppingAbanRight.draw();

        function fillChartLines(linesSelector, end, format, padding) {
            var padding = (!padding) ? 1.1 : padding;
            var format = (!format) ? '0.0' : format;
            var divs = $(linesSelector);
            var ar = Array(divs.length);
            end = end * padding // pad it by 10 percent
            $(ar).map(function (idx) {
                var n = (idx + 1) * (end / divs.length);
                ar[idx] = numeral(n).format(format);
                // update div text
                $(divs[divs.length - (idx+1)]).text(ar[idx]);
            });
            console.log(divs);
            console.log(ar)
        }

        function animateLoyalty(data, customerData) {
            heartLeft.percent = 88;
            heartLeft.animate();
            heartRight.percent = 60;
            heartRight.animate();
        }

        function animateShoppingAbandonment(data, customerData) {
            shoppingAbanLeft.percent = 70;
            shoppingAbanLeft.animate();
            shoppingAbanRight.percent = 90;
            shoppingAbanRight.animate();
        }

        var scoreCard = d3.scoreCard("div#score-card");

        // BEGIN Conversion Radial Gauge
         var gauge = new RadialGauge({
            renderTo: 'gauge-id', // identifier of HTML canvas element or element itself
            width: 250,
            height: 250,
            units: '',
            title: false,
            value: 0,
            minValue: 0,
            maxValue: 4,
            startAngle: 90,
            ticksAngle: 180,
            majorTicks: [
                '0','1','1.5','2','2.5','3','3.5','4'
            ],
            minorTicks: 5,
            strokeTicks: false,
            highlights: [
                {"from": 0, "to": 1, "color": "rgba(250, 48, 35, 1)"},
                {"from": 3, "to": 4, "color": "rgba(58, 198, 40, 1)"}
            ],
            colorPlate: '#fff',
            colorMajorTicks: '#a2a3a3',
            colorMinorTicks: '#a2a3a3',
            colorTitle: '#a2a3a3',
            colorUnits: '#a2a3a3',
            colorNumbers: '#a2a3a3',
            colorNeedleStart: '#a2a3a3',
            colorNeedleEnd: '#a2a3a3',

            valueBox: false,
            animationRule: 'bounce',
            borders: false,
            borderShadowWidth: 0
        });
        // draw initially
        gauge.draw();

        // END Conversion Radial Gauge

    </script>
@endsection
