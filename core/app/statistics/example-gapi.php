<?php
/**
 * @brief In the controller class
 * @see Gino\App\Statistics\statistics::analytics()
 */

// Filters
$filter_type = null;
$filter_value = null;

$filter_type = '-visits';
$filter_value = 'country == Italy && browser == Firefox || browser == Chrome';
// /Filters

$ga = new \gapi(GOOGLE_ANALYTICS_VIEW_ACCOUNT, $file_key);

$ga->requestReportData(GOOGLE_ANALYTICS_VIEW_ID, array('browser', 'browserVersion'), array('pageviews', 'visits'), $filter_type, $filter_value);

/**
 * @brief Using the GAPI filter control
 * @see https://github.com/erebusnz/gapi-google-analytics-php-interface/blob/wiki/UsingFilterControl.md
 * 
 * Note: OR || operators are calculated first, before AND &&.
 * There are no brackets () for precedence and no quotes are required around parameters.
 * 
 * Do not use brackets () for precedence, these are only valid for use in regular expressions operators!
 * 
 * The below filter represented in normal PHP logic would be:
 * country == 'United States' && ( browser == 'Firefox || browser == 'Chrome')
 */

/**
 * @brief In the view
 * @var
 * - **ga**: object \gapi
 */
 ?>
<div class="pageviews">
	<h2>Google Analytics Data</h2>
	
	<? if(count($ga->getResults())): ?>
		<table class="table table-bordered">
        	<tr>
        		<th>Browser &amp; Browser Version</th>
        		<th>Pageviews</th>
        		<th>Visits</th>
        	</tr>
        	<? foreach($ga->getResults() as $result): ?>
        	<tr>
        		<td><?= $result ?></td>
        		<td><?= $result->getPageviews() ?></td>
        		<td><?= $result->getVisits() ?></td>
        	</tr>
        	<? endforeach ?>
        </table>
        
        <table class="table table-bordered">
        	<tr>
        		<th>Total Results</th>
        		<td><?= $ga->getTotalResults() ?></td>
        	</tr>
        	<tr>
        		<th>Total Pageviews</th>
        		<td><?= $ga->getPageviews() ?></td>
        	</tr>
        	<tr>
        		<th>Total Visits</th>
        		<td><?= $ga->getVisits() ?></td>
        	</tr>
        	<tr>
        		<th>Result Date Range</th>
        		<td><?= $ga->getStartDate() ?> to <?= $ga->getEndDate() ?></td>
        	</tr>
        </table>
	<? endif ?>
</div>

