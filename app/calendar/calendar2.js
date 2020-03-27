
document.addEventListener('DOMContentLoaded', function() {
	var calendarEl = document.getElementById('calendar');
	/*
	$.ajax({
		dataType: "json",
		url: url,
		data: data,
		success: success
	});
	
	$.getJSON( "ajax/test.json", function( data ) {
		var items = [];
		$.each( data, function( key, val ) {
			items.push( "<li id='" + key + "'>" + val + "</li>" );
		});
 
		$( "<ul/>", {
			"class": "my-new-list",
			html: items.join( "" )
		}).appendTo( "body" );
	});
	*/

	var calendar = new FullCalendar.Calendar(calendarEl, {
		//plugins: [ 'dayGrid' ]
		plugins: [ 'dayGrid', 'timeGrid', 'list', 'interaction', 'bootstrap', ],	// an array of strings!
		themeSystem: 'bootstrap',
		dateClick: function() {
			alert('a day has been clicked!');
		},
		/*
		defaultView: 'dayGridMonth',
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,timeGridWeek,timeGridDay'
		},*/
		events: [
			
		]
	});
	calendar.render();
});

/*
var request = new Request.JSON({
        	url: this.options.json_url,
            method: 'post',
            data: jparam + 'month=' + (this.current.getMonth() + 1) + '&year=' + this.current.getFullYear() + (this.request_data ? '&' + this.request_data : ''),
            onRequest: function() {
                self.spinner.show();
            },
            onFailure: function() {
                self.spinner.hide();
                alert(self.locale.get('cant-fetch-data'));
            },
            onError: function() {
                self.spinner.hide();
                alert(self.locale.get('invalid-data'));
            },
            onSuccess: function(responseJSON, responseText) {
                self.items = responseJSON;
                self.addMonthData();
                self.spinner.hide();
                self.hideContainerOnMonthChange();
            }
        }).send();

    },
  
  
  {
    title: 'All Day Event',
    start: '2020-02-01'
  },
  {
    title: 'Long Event',
    start: '2020-02-07',
    end: '2020-02-10'
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: '2020-02-09T16:00:00'
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: '2020-02-16T16:00:00'
  },
  {
    title: 'Conference',
    start: '2020-02-11',
    end: '2020-02-13'
  },
  {
    title: 'Meeting',
    start: '2020-02-12T10:30:00',
    end: '2020-02-12T12:30:00'
  },
  {
    title: 'Lunch',
    start: '2020-02-12T12:00:00'
  },
  {
    title: 'Meeting',
    start: '2020-02-12T14:30:00'
  },
  {
    title: 'Birthday Party',
    start: '2020-02-13T07:00:00'
  },
  {
    title: 'Click for Google',
    url: 'http://google.com/',
    start: '2020-02-28'
  }
*/
