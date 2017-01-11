<section class="admin">
<?= $alert ?>
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

<div class="row" style="margin: 20px 0;">
    <div class="col-md-6">
        <section class="panel">
            <h3>Traffico</h3>
            <h5>Sessioni e utenti, ultimi 15 giorni</h5>
            <div id="chart-1-container"></div>
        </section>
    </div>
    <div class="col-md-6">
        <section class="panel">
            <h3>Pagine</h3>
            <h5>Visualizzazioni di pagina, ultimi 15 giorni</h5>
            <div id="chart-2-container"></div>
        </section>
    </div>
</div>
<div class="row" style="margin: 20px 0;">
    <div class="col-md-6">
        <section class="panel">
            <h3>Browser</h3>
            <h5>Ultimi 15 giorni</h5>
            <div id="chart-3-container"></div>
        </section>
    </div>
    <div class="col-md-6">
        <section class="panel">
            <h3>Acquisizione</h3>
            <h5>Referral, ultimi 15 giotni</h5>
            <div id="chart-4-container"></div>
        </section>
    </div>
</div>
<div class="row" style="margin: 20px 0;">
    <div class="col-md-6">
        <section class="panel">
            <h3>Audience</h3>
            <h5>Paesi, ultimi 15 giorni</h5>
            <div id="chart-5-container"></div>
        </section>
    </div>
    <div class="col-md-6">
        <section class="panel">
            <h3>Social</h3>
            <h5>Interazioni, ultimi 15 giorni</h5>
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
    });
    /**
     * Create a new ViewSelector2 instance to be rendered inside of an
     * element with the id "view-selector-container".
     */
    var viewSelector = new gapi.analytics.ext.ViewSelector2({
        container: 'view-selector-container',
    })
    .execute();
    /**
     * Update the activeUsers component, the Chartjs charts, and the dashboard
     * title whenever the user changes the view.
     */
    viewSelector.on('viewChange', function(data) {
        // Start tracking active users for this view.
        activeUsers.set(data).execute();
    });
    /**
     * Creates a new DataChart instance showing sessions over the past 15 days.
     */
    var dataChart1 = new gapi.analytics.googleCharts.DataChart({
        query: {
            'ids': 'ga:<?= $view_id ?>',
            'start-date': '15daysAgo',
            'end-date': 'yesterday',
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
            'ids': 'ga:<?= $view_id ?>',
            'start-date': '15daysAgo',
            'end-date': 'yesterday',
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
            'ids': 'ga:<?= $view_id ?>',
            'start-date': '15daysAgo',
            'end-date': 'yesterday',
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
            'ids': 'ga:<?= $view_id ?>',
            'start-date': '15daysAgo',
            'end-date': 'yesterday',
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
            'ids': 'ga:<?= $view_id ?>',
            'start-date': '15daysAgo',
            'end-date': 'yesterday',
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
            'ids': 'ga:<?= $view_id ?>',
            'start-date': '15daysAgo',
            'end-date': 'yesterday',
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
</section>
