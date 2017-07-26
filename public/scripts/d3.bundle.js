/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports) {

	/* WEBPACK VAR INJECTION */(function(global) {function toolTip(el) {
	    var tooltip = d3.select("div.d3-tooltip");

	    if (tooltip.empty()) tooltip = d3.select("body").append("div")
	            .attr("class", "d3-tooltip")
	            .style("opacity", 0);

	     el
	     .on('mouseover', function(d) {
	         tooltip.transition()
	           .duration(200)
	           .style("opacity", .9);
	         tooltip.html(d)
	           .style("left", (d3.event.pageX) + "px")
	           .style("top", (d3.event.pageY) + "px");
	      })
	      .on("mouseout", function(d) {
	         tooltip.transition()
	           .duration(500)
	           .style("opacity", 0);
	      });
	}

	global.d3.iconGraph = function(selector){
	   var element = d3.select(selector);

	   var iconGraph = {
	       draw: function(){
	           if(!this.percent)
	              this.percent = 100;

	           if(!this.duration)
	              this.duration = 1500;


	           if (this.width && this.height) {
	             element
	                 .style('width', this.width)
	                 .style('position', 'relative')
	                 .style('height', this.height);

	             var d3graph = element.append('div');

	             d3graph.style('width', this.width)
	             .style('height', this.height)
	             .style('position', 'absolute')
	             .style('z-index', '1000')
	             .style('clip','rect(' + this.height + ' ' + this.width + ' ' + this.height + ' 0px)');
	           }

	           toolTip(d3graph);

	           if (this.emptyGraph) element.style('background', 'url(' + this.emptyGraph + ')');

	           if (this.fullGraph) d3graph.style('background','url('+ this.fullGraph +')');
	       },
	       animate: function() {
	           var height = parseFloat(this.height.replace( /^\D+/g, ''))*(1 - (this.percent/100));
	           var t = d3.transition().ease(d3.easeQuadOut).duration(this.duration);

	           var d3graph = element.select('div');
	           d3graph.transition(t)
	            .style('clip','rect(' + height + 'px ' + this.width + ' ' + this.height + ' 0px)');
	       }
	   };

	  return iconGraph;
	}


	global.d3.barGraph = function(selector) {
	  var element = d3.select(selector)

	  var barGraph = {
	    properties: function(props) {
	      if(!props.duration)
	         props.duration = 1500;

	      var tooltip = d3.select('.d3-tooltip') || d3.select("body").append("div")
	        .attr("class", "d3-tooltip")
	        .style("opacity", 0);

	      var yScale = d3.scaleLinear()
	        .domain([0, d3.max(props.data)])
	        .range([0, props.height]);

	      var xScale = d3.scaleBand()
	        .domain(d3.range(0, props.data.length))
	        .padding(0.2)
	        .range([0, props.width]);

	      var myChart = element.append('svg')
	          .attr('width', props.width)
	          .attr('height', props.height)
	          .append('g')
	          .style('background', '#C9D7D6')
	          .selectAll('rect')
	          .data(props.data)
	          .enter()
	          .append('rect')
	          .style('fill', function(d, i) {
	              return props.colors[i]; // color(i);
	          })
	          .attr('width', xScale.bandwidth())
	          .attr('x', function(d, i) {
	              return xScale(i);
	          })
	          .attr('height', 0)
	          .attr('y', props.height)

	      toolTip(myChart);

	      myChart.transition()
	          .attr('height', function(d){
	              return yScale(d);
	          })
	          .attr('y', function(d){
	              return props.height - yScale(d);
	          })
	          .delay(function(d, i){
	              return i * 20;
	          })
	          .duration(props.duration)
	          .ease(d3.easeQuadOut)
	    }
	  }

	  return barGraph;
	}


	global.d3.scoreCard = function(selector) {
	    var element = d3.select(selector)

	    var colors = {
	        'pink': '#E1499A',
	        'yellow': '#f0ff08',
	        'green': '#47e495'
	    };

	    var color = '#ffffff';

	    var radius = 75;
	    var border = 5;
	    var padding = 3;
	    var startPercent = 0.01;
	    var endPercent = 0.53;

	    var twoPi = Math.PI * 2;
	    var formatPercent = d3.format('.0%');
	    var formatNumber = d3.format('0');
	    var boxSize = (radius + padding) * 2;

	    var count = Math.abs((endPercent - startPercent) / 0.01);
	    var step = endPercent < startPercent ? -0.01 : 0.01;

	    var parent = element;

	    var svg = parent.append('svg')
	        .attr('width', boxSize)
	        .attr('height', boxSize);

	    var arc = d3.arc()
	        .startAngle(0)
	        .innerRadius(radius)
	        .outerRadius(radius - border);

	    var defs = svg.append('defs');

	    var filter = defs.append('filter')
	        .attr('id', 'blur');

	    /*
	    filter.append('feGaussianBlur')
	        .attr('in', 'SourceGraphic')
	        .attr('stdDeviation', '7');
	    */

	    var g = svg.append('g')
	        .attr('transform', 'translate(' + boxSize / 2 + ',' + boxSize / 2 + ')');

	    var meter = g.append('g')
	        .attr('class', 'progress-meter');

	    meter.append('path')
	        .attr('class', 'background')
	        .attr('fill', '#ccc')
	        .attr('fill-opacity', 0.5)
	        .attr('d', arc.endAngle(twoPi));

	    var foreground = meter.append('path')
	        .attr('class', 'foreground')
	        .attr('fill', color)
	        .attr('fill-opacity', 1)
	        .attr('stroke', color)
	        .attr('stroke-width', 5)
	        .attr('stroke-opacity', 1)
	        .attr('filter', 'url(#blur)');

	    var front = meter.append('path')
	        .attr('class', 'foreground')
	        .attr('fill', color)
	        .attr('fill-opacity', 1);

	    var numberText = meter.append('text')
	        .attr('fill', '#fff')
	        .attr('text-anchor', 'middle')
	        .style("font-size", "3em")
	        .attr('dy', '-.10em');

	    var justText = meter.append('text')
	        .attr('fill', '#fff')
	        .attr('text-anchor', 'middle')
	        .attr('dy', '1.0em')
	        .text('Out of 100')
	        .style("font-size", "1.5em");

	    var progress = startPercent;

	    numberText.text(formatNumber(0));

	    var scoreCard = {
	      properties: function(props) {

	      },

	      animate: function() {
	        function updateProgress(progress) {
	            foreground.attr('d', arc.endAngle(twoPi * progress));
	            front.attr('d', arc.endAngle(twoPi * progress));
	            numberText.text(formatNumber(progress*100));
	        }

	        (function loops() {
	            updateProgress(progress);
	            if (count > 0) {
	                count--;
	                progress += step;
	                setTimeout(loops, 10);
	            }
	        })();
	      }
	    }

	    return scoreCard;
	}

	global.d3.countUp = function(selector) {
	  var element = d3.select(selector);

	  var countUp = {
	    properties: function(props) {
	      if(!props.duration)
	        props.duration = 1500;

	      var format = d3.format(props.format);
	      element
	        .transition()
	        .duration(props.duration)
	        .tween("text", function() {
	          var that = d3.select(this),
	            i = d3.interpolateNumber(props.start, props.end);
	            return function(t) {
	              that.text(format(i(t)));
	            };
	      });
	    }
	  }

	  return countUp;
	}

	/* WEBPACK VAR INJECTION */}.call(exports, (function() { return this; }())))

/***/ }
/******/ ]);