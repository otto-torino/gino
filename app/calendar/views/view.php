<?php
namespace Gino\App\Calendar;
/**
* @file view.php
* @brief Template per la vista calendario
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **json_url**: string, indirizzo per recuperare gli eventi di un mese (da visualizzare nel calendario)
* - **url**: string, indirizzo per recuperare gli eventi di un mese (da visualizzare in un elenco)
*
* @version 1.0.0
* @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>

<section id="view-calendar-<?= $instance_name ?>">
    <h1><?= _('Agenda') ?></h1>
    <div id="calendar"></div>
    <div id="calendar-controllers" style="display: none;">
        <span class="fa fa-2x fa-calendar" id="month_view"></span>
        <span class="link fa fa-2x fa-bars" onclick="gino.ajaxRequest('post', '<?= $url ?>', 'month=' + (calendar.getCurrentMonth() + 1) + '&year=' + calendar.getCurrentYear(), 'calendar-list', {callback: function() {$('calendar-list').style.display = 'block';}})"></span>
    </div>
    <!-- container list -->
    <div id="calendar-list"></div>
    
    <script>
        var calendar = new agenda.Calendar({
            json_url: '<?= $json_url ?>',
            month_view_ctrl: 'month_view',
            hide_container: 'calendar-list',
            onComplete: function() { $('calendar-controllers').style.display = 'block'; }
        });
        calendar.render('calendar');
    </script>
</section>


<? // @endcond ?>
