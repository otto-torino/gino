<?php
/**
 * @file options.php
 * @brief Impostazioni dei campi delle opzioni del controller
 * @description Il formato dei campi:
 *  (string) fieldname => [
 *    'default' => mixed, 
 *    'label' => mixed (string, array [title, text(helptext)]), 
 *    'value' => mixed, 
 *    'required' => boolean,
 *    'trns' => boolean,
 *    'editor' => boolean,
 *    'footnote' => string,
 *    'section' => boolean, 
 *    'section_title' => string, 
 *    'section_description' => string
 *  ]
 * 
 * @see Gino.Controller
 * @see Gino.Options
 */
namespace Gino\App\Calendar;

$options = [
    'monday_first_week_day' => array(
        'default' => 1,
        'label' => _('Lunedì primo giorno della settimana'),
        'section' => true,
        'section_title' => _('Calendario'),
    ),
    'day_chars' => array(
        'default' => 1,
        'label' => _('Numero di caratteri rappresentazione giorno'),
    ),
    'open_modal' => array(
        'default' => 0,
        'label' => _('Visualizza il dettaglio aprendo una modale'),
    ),
    'items_for_page' => array(
        'default' => 10,
        'label' => _('Appuntamenti per pagina'),
        'section' => true,
        'section_title' => _('Archivio'),
    ),
];
