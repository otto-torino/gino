<?php
namespace Gino\App\Statistics;
/**
 * @file analytics.php
 * @brief Template per la vista delle statistiche google analytics
 *
 * Variabili disponibili:
 * - **token**: string
 * - **ga_view_id**: integer
 * - **ga**: object \gapi
 * - **start_date**: string
 * - **end_date**: string
 * - **filter**: string
 * - **label**: string
 *
 * @version 1.0.0
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 * 
 * Overview of the Google Analytics Embed API
 * @see https://developers.google.com/analytics/devguides/reporting/embed/v1/
 */
?>

<div id="analytics-stats">

<? if($token): ?>
	<!-- Load the library -->
	<script>
	(function(w,d,s,g,js,fs){
		g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
		js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
		js.src='https://apis.google.com/js/platform.js';
		fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
		}(window,document,'script'));
	</script>

	<!-- Include the ActiveUsers component script. -->
	<script src="https://ga-dev-tools.appspot.com/public/javascript/embed-api/components/active-users.js"></script>
	<script src="https://ga-dev-tools.appspot.com/public/javascript/embed-api/components/view-selector2.js"></script>

	<h4 class="italic-title">Google Analytics <span class="btn btn-default" id="active-users-container"></span></h4>
	<!-- this is just to make the view selector work, no need to display it -->
	<div id="view-selector-container" style="display: none"></div>
	
	<?= $filter ?>

	<div class="row" style="margin: 20px 0;">
		<div class="col-md-6">
			<section class="panel">
				<h1>Traffic</h1>
				<h2>Sessions and Users, <?= $label ?></h2>
				<div id="chart-1-container"></div>
			</section>
		</div>
		<div class="col-md-6">
			<section class="panel">
				<h1>Popular</h1>
				<h2>Page views, <?= $label ?></h2>
				<div id="chart-2-container"></div>
			</section>
		</div>
	</div>
	<div class="row-fluid" style="margin: 20px 0;">
		<div class="col-md-6">
			<section class="panel">
				<h1>Top Browsers</h1>
				<h2><?= ucfirst($label) ?></h2>
				<div id="chart-3-container"></div>
			</section>
		</div>
		<div class="col-md-6">
			<section class="panel">
				<h1>Acquisition</h1>
				<h2>Referral Traffic, <?= $label ?></h2>
				<div id="chart-4-container"></div>
			</section>
		</div>
	</div>
	<div class="row-fluid" style="margin: 20px 0;">
		<div class="col-md-6">
			<section class="panel">
				<h1>Audience</h1>
				<h2>Countries, <?= $label ?></h2>
				<div id="chart-5-container"></div>
			</section>
		</div>
		<div class="col-md-6">
			<section class="panel">
				<h1>Social</h1>
				<h2>Interactions, <?= $label ?></h2>
				<div id="chart-6-container"></div>
			</section>
		</div>
	</div>

	<script>
	gapi.analytics.ready(function() {

	    /**
	     * Authorize the user with an access token obtained server side.
	     */
	    gapi.analytics.auth.authorize({
	        'serverAuth': {
	            'access_token': '<?= $token ?>'
	        }
	    });

	    /**
	     * Create a new ActiveUsers instance to be rendered inside of an
	     * element with the id "active-users-container" and poll for changes every
	     * five seconds.
	     */
	    var activeUsers = new gapi.analytics.ext.ActiveUsers({
	        container: 'active-users-container',
	        pollingInterval: 5
	    }).execute();
	    /**
	     * Create a new ViewSelector2 instance to be rendered inside of an
	     * element with the id "view-selector-container".
	     */
	    var viewSelector = new gapi.analytics.ext.ViewSelector({
	        container: 'view-selector-container',
	    })
	    
	    /**
	     * Update the activeUsers component, the Chartjs charts, and the dashboard title 
	     * whenever the user changes the view.
	     */
	     viewSelector.on('viewChange', function(data) {
	         // Start tracking active users for this view.
	         activeUsers.set(data).execute();
	     });
	     viewSelector.execute()
	     
	     let baseQuery = {
	         'ids': 'ga:<?= $ga_view_id ?>',
	         'start-date': '<?= $start_date ?>',
	         'end-date': '<?= $end_date ?>'
	     }
	     
	    /**
	     * Creates a new DataChart instance showing sessions over the past 15 days.
	     */
	    var dataChart1 = new gapi.analytics.googleCharts.DataChart({
	        query: {
	        	...baseQuery,
	            'metrics': 'ga:sessions,ga:users',
	            'dimensions': 'ga:date'
	        },
	        chart: {
	            'container': 'chart-1-container',
	            'type': 'LINE',
	            'options': {
	                'width': '100%'
	            }
	        }
	    });
	    dataChart1.execute();
	    /**
	     * Creates a new DataChart instance showing top 5 most popular pages
	     */
	    var dataChart2 = new gapi.analytics.googleCharts.DataChart({
	        query: {
	        	...baseQuery,
	            'metrics': 'ga:pageviews',
	            'dimensions': 'ga:pagePath',
	            'sort': '-ga:pageviews',
	            'max-results': 7
	        },
	        chart: {
	            'container': 'chart-2-container',
	            'type': 'PIE',
	            'options': {
	                'width': '100%',
	                'pieHole': 4/9,
	            }
	        }
	    });
	    dataChart2.execute();
	    /**
	     * Creates a new DataChart instance showing top borwsers
	     */
	    var dataChart3 = new gapi.analytics.googleCharts.DataChart({
	        query: {
	        	...baseQuery,
	            'metrics': 'ga:sessions',
	            'dimensions': 'ga:browser',
	            'sort': '-ga:sessions',
	            'max-results': 7
	        },
	        chart: {
	            'container': 'chart-3-container',
	            'type': 'PIE',
	            'options': {
	                'width': '100%',
	                'pieHole': 4/9,
	            }
	        }
	    });
	    dataChart3.execute();
	    /**
	     * Creates a new DataChart instance showing top referral
	     */
	    var dataChart4 = new gapi.analytics.googleCharts.DataChart({
	        query: {
	        	...baseQuery,
	            'metrics': 'ga:sessions',
	            'dimensions': 'ga:source',
	            'sort': '-ga:sessions',
	            'max-results': 7
	        },
	        chart: {
	            'container': 'chart-4-container',
	            'type': 'PIE',
	            'options': {
	                'width': '100%',
	                'pieHole': 4/9,
	            }
	        }
	    });
	    dataChart4.execute();
	    /**
	     * Creates a new DataChart instance showing top visitors continents
	     */
	    var dataChart5 = new gapi.analytics.googleCharts.DataChart({
	        query: {
	        	...baseQuery,
	            'metrics': 'ga:sessions',
	            'dimensions': 'ga:country',
	            'sort': '-ga:sessions',
	            'max-results': 7
	        },
	        chart: {
	            'container': 'chart-5-container',
	            'type': 'PIE',
	            'options': {
	                'width': '100%',
	                'pieHole': 4/9,
	            }
	        }
	    });
	    dataChart5.execute();
	    /**
	     * Creates a new DataChart instance showing social interactions over the past 15 days.
	     */
	    var dataChart6 = new gapi.analytics.googleCharts.DataChart({
	        query: {
	        	...baseQuery,
	            'metrics': 'ga:socialInteractions',
	            'dimensions': 'ga:socialInteractionNetwork',
	            'sort': '-ga:socialInteractions',
	            'max-results': 7
	        },
	        chart: {
	            'container': 'chart-6-container',
	            'type': 'PIE',
	            'options': {
	                'width': '100%',
	                'pieHole': 4/9,
	            }
	        }
	    });
	    dataChart6.execute();
	});
	</script>
<? endif ?>
</div><!-- /analytics-stats -->

