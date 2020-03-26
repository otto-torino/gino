<?php
namespace Gino\App\Calendar;
/**
* @file calendar.php
* @brief Template per la vista calendario
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **select**: html, input select per la scelta categoria
* - **router**: \Gino\Router, istanza di Gino.Router
* - **json_url**: string, url per ricavare eventi mese anno in json
* 
* @version 1.0.0
* @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section id="agenda-calendar-<?= $instance_name ?>">
    <h1><?= _('Calendario') ?></h1>
    
    <div id="calendar"></div>
    
<?= $script ?>
</section>
<? // @endcond ?>
