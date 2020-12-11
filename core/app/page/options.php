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
namespace Gino\App\Page;

$registry = \Gino\Registry::instance();
$db = $registry->db;

$res_newsletter = $db->getFieldFromId(TBL_MODULE_APP, 'id', 'name', 'newsletter');
if($res_newsletter) {
    $newsletter_module = TRUE;
}
else {
    $newsletter_module = FALSE;
}

$options = [
    "last_title" => array(
        'default' => _("Pagine recenti"),
        'label' => _("Titolo visualizzazione ultime pagine"),
        'section' => true,
        'section_title' => _('Opzioni vista ultime pagine'),
        'section_description' => "<p>"._("Il template verrà utilizzato per ogni pagina ed inserito all'interno di una section")."</p>"
    ),
    "last_number" => array(
        'default' => 10,
        'label' => _("Numero di elementi visualizzati"),
    ),
    "last_tpl_code"=>array(
        'default' => null,
        'label' => _("Template singolo elemento vista ultime pagine"),
        'footnote' => page::explanationTemplate()
    ),
    "showcase_title" => array(
        'default' => _("In evidenza"),
        'label' => _("Titolo vetrina pagine più lette"),
        'section' => true,
        'section_title' => _('Opzioni vista vetrina pagine più lette'),
        'section_description' => "<p>"._("Il template verrà utilizzato per ogni pagina ed inserito all'interno di una section")."</p>"
    ),
    "showcase_number" => array(
        'default' => 3,
        'label' => _("Numero di elementi in vetrina"),
    ),
    "showcase_auto_start" => array(
        'default' => 1,
        'label' => _("Avvio automatico animazione"),
    ),
    "showcase_auto_interval" => array(
        'default' => 5000,
        'label' => _("Intervallo animazione automatica (ms)"),
    ),
    "showcase_tpl_code" => array(
        'default' => null,
        'label' => _("Template singolo elemento vista vetrina"),
        'footnote' => page::explanationTemplate(),
    ),
    "entry_tpl_code" => array(
        'default' => null,
        'label' => _("Template vista dettaglio pagina"),
        'footnote' => page::explanationTemplate(),
        'section'=>true,
        'section_title'=>_('Opzioni vista pagina'),
        'section_description'=>"<p>"._("Il template verrà utilizzato per ogni pagina ed inserito all'interno di una section")."</p>"
    ),
    "box_tpl_code" => array(
        'default' => null,
        'label' => _("Template vista dettaglio pagina"),
        'footnote' => page::explanationTemplate(),
        'section' => true,
        'section_title' => _('Opzioni vista pagina inserita nel template'),
        'section_description' => "<p>"._("Il template verrà utilizzato per ogni pagina ed inserito all'interno di una section")."</p>"
    ),
    "comment_moderation" => array(
        'default' => 0,
        'label' => array(_("Moderazione commenti"), _("In tal caso i commenti dovranno essere pubblicati da un utente iscritto al gruppo dei 'pubblicatori'. Tali utenti saranno notificati della presenza di un nuovo commento con una email")),
        'section' => true,
        'section_title' => _('Opzioni commenti')
    ),
    "comment_notification" => array(
        'default' => 1,
        'label' => array(_("Notifica commenti"), _("In tal caso l'autore della pagina riceverà una email per ogni commento pubblicato")),
    ),
    "newsletter_entries_number" => array(
        'default' => 5,
        'label' => _('Numero di elementi presentati nel modulo newsletter'),
        'section' => true,
        'section_title' => _('Opzioni newsletter'),
        'section_description' => $newsletter_module
        ? "<p>"._('La classe si interfaccia al modulo newsletter di gino installato sul sistema')."</p>"
        : "<p>"._('Il modulo newsletter non è installato')."</p>",
    ),
    "newsletter_tpl_code" => array(
        'default' => null,
        'label' => _("Template pagina in inserimento newsletter"),
        'footnote' => page::explanationTemplate(),
    ),
];
