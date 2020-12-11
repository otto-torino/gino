<?php
/**
 * @file class.Calendar.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Javascript
 */
namespace Gino;

/**
 * @brief Contiene i metodi per includere alcuni javascript
 * 
 * #LIBRARY
 * 
 * @link https://fullcalendar.io/docs/
 * @link https://fullcalendar.io/docs/event-object
 * 
 * #USAGE
 * 
 * ## PHP
 * Nel metodo che richiama il calendario:
 * 
 * @code
 * Loader::import('class', array(
 *   '\Gino\Calendar',
 * ));
 *      
 * $cal = new \Gino\Calendar();
 * $cal->importFiles();
 * 
 * $events = [[
 *   'title' => string,
 *   'description' => string,
 *   'start' => date,
 *   'end' => date,
 *   'url' => string
 * ]
 * $date_click = "function(info) {
 *   alert('Clicked on: ' + info.dateStr);
 * }";
 * 
 * $dict = array(
 *   'script' => $cal->script([
 *     'events' => $events,
 *     'date_click' => $date_click,
 *     //'insert_modal' => true
 *   ]),
 * );
 * @endcode
 * 
 * ## HTML (VIEW)
 * 
 * @code
 * <div id="calendar"></div>
 * <?= $script ?>
 * @endcode
 * 
 * #EVENT_OPTIONS
 * L'evento associato alle date del calendario può avere le seguenti opzioni:
 * - 'title' => string
 * - 'description' => string
 * - 'start' => string '2020-02-01' -> data di inizio evento
 * - 'end' => string '2020-02-10'   -> data di fine evento (eventi che continuano su più giorni)
 * - 'groupId' => string '999'      -> codice dell'evento (eventi ripetuti su più date)
 *                                  -> ogni evento del gruppo deve riportare questo codice
 * - url => string                  -> indirizzo web
 * 
 * La data può essere nei formati:
 * - '2020-02-01'
 * - '2020-02-09T16:00:00'  (riporta anche l'orario)
 * 
 * 
 * #FUNZIONI
 * 
 * ## dateClick
 * @link https://fullcalendar.io/docs/dateClick
 * 
 * @example
 * dateClick: function(info) {
 *   alert('Clicked on: ' + info.dateStr);
 *   alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
 *   alert('Current view: ' + info.view.type);
 *   // change the day's background color just for fun
 *   info.dayEl.style.backgroundColor = 'red';
 * }
 */
class Calendar {

    private $_modal_id;
    
    public function __construct($options=[]) {
        
        $modal_id = \Gino\gOpt('modal_id', $options, 'fullCalModal');
        
        $this->_registry = Registry::instance();
        $this->_modal_id = $modal_id;
    }
    
    /**
     * @brief File da includere
     * @return null
     */
    public function importFiles() {
        
        $this->_registry->addCss(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/core/main.css');
        $this->_registry->addCss(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/daygrid/main.css');
        $this->_registry->addCss(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/list/main.css');
        $this->_registry->addCss(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/bootstrap/main.css');
        
        $this->_registry->addJs(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/core/main.js');
        $this->_registry->addJs(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/daygrid/main.js');
        $this->_registry->addJs(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/timegrid/main.js');
        $this->_registry->addJs(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/list/main.js');
        $this->_registry->addJs(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/interaction/main.js');
        $this->_registry->addJs(SITE_LIBRARIES.'/fullcalendar-4.4.0/packages/bootstrap/main.js');
        
        return null;
    }
    
    private function insertModal() {
        
        $view = new View(null, 'modal');
        $dict = array(
            'modal_id' => $this->_modal_id,
            'modal_title_id' => null,
            'vertically_centered' => true,
            'size_modal' => null,
            'title' => null,
            'body' => null,
            'close_button' => true,
            'save_button' => false,
        );
        return $view->render($dict);
    }
    
    /**
     * @brief Costruisce il javascript che gestisce il calendario
     * 
     * @param array $options
     *   - @b element (string): valore ID del container
     *   - @b plugins (mixed): plugin della libraria
     *       @a string, @example "'dayGrid', 'bootstrap'"
     *       @a array, @example ['dayGrid', 'bootstrap']
     *   - @b default_view (string): default @a dayGridMonth
     *   - @b date_click (string): evento al click sulla data
     *       @example function() { alert('a day has been clicked!'); }
     *   - @b events (mixed): elenco degli eventi
     *       @a array, array di array
     *       @a string, elenco in formato json
     *       @example [{"title":"Title1","start":"2020-02-26"},
     *                {"title":"Title2","start":"2020-02-29"}]
     *   - @b header (string):
     *       @example
     *       {
     *         left: 'prev,next today',
     *         center: 'title',
     *         right: 'dayGridMonth,timeGridWeek,timeGridDay'
     *       }
     *   - @b event_click (boolean): abilita l'apertura di una modale al click sull'evento (default true)
     *   - @b insert_modal (boolean): inserisce la modale nell'HTML (default true)
     * @return string
     */
    public function script($options=[]) {
        
        $element = \Gino\gOpt('element', $options, 'calendar');
        $plugins = \Gino\gOpt('plugins', $options, ['dayGrid', 'timeGrid', 'list', 'interaction', 'bootstrap']);    // an array of strings!
        $default_view = \Gino\gOpt('default_view', $options, 'dayGridMonth');
        $date_click = \Gino\gOpt('date_click', $options, null);
        $header = \Gino\gOpt('header', $options, null);
        $event_click = \Gino\gOpt('event_click', $options, true);
        $insert_modal = \Gino\gOpt('insert_modal', $options, true);
        
        if(is_array($plugins)) {
            $p = '';
            foreach ($plugins as $value) {
                $p .= "'$value',";
            }
            
            $plugins = $p;
        }
        
        $events = \Gino\gOpt('events', $options, '');
        if(!is_string($events)) {
            $events = json_encode($events);
        }
        
$buffer = "
<script type=\"text/javascript\">
$(function() {

	var calendarEl = document.getElementById('".$element."');

	var calendar = new FullCalendar.Calendar(calendarEl, {
		plugins: [ ".$plugins." ],
		themeSystem: 'bootstrap',
		height: 'parent',";
        if($date_click) {
            $buffer .= "
        dateClick: $date_click,";
        }
        if($header) {
            $buffer .= "
        header: $header,";
        }
        $buffer .= "
        defaultView: '$default_view',
		events: $events,";
        if($event_click) {
            $buffer .= "
        eventClick:  function(info) {
            //alert('Event: ' + info.event.title);
            $('#modalTitle').html(info.event.title);
            $('#modalBody').html(info.event.extendedProps.description);
            $('#eventUrl').attr('href',info.event.url);
            $('#".$this->_modal_id."').modal();
        }";
        }
        $buffer .= "
	});
	calendar.render();
});
</script>";
        
        if($insert_modal) {
            $buffer .= $this->insertModal();
        }
        return $buffer;
    }
    
    /**
     * @brief Imposta la data conclusiva dell'evento
     * 
     * @param string $date in the format Y-m-d
     * @param integer $days days number
     * @return string in the format Y-m-d
     */
    public function setEndDate($date, $days) {
        
        $obj_date = new \Datetime($date);
        $days = '+'.$days.' days';
        $obj_date->modify($days);
        
        return $obj_date->format('Y-m-d');
    }
    
}
