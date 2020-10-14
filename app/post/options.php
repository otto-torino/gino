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
namespace Gino\App\Post;

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
    'last_post_number' => [
        'default' => 3,
        'label' => _("Numero ultimi post"),
        'section' => true,
        'section_title' => _('Ultimi post')
    ],
    'last_slideshow_view' => [
        'default' => false,
        'label' => array(_("Visualizza lo slideshow nella vista"), _("impostare i post da mostrare nello slideshow")),],
    'last_slideshow_number' => [
        'default' => 3,
        'label' => _("Numero di post nello slideshow"),],
    'list_nfp' => ['default' => 5, 'label'=>_("Numero post per pagina"), 'section'=>true, 'section_title'=>_('Archivio post')],
    'showcase_post_number' => ['default' => 5, 'label' => _("Numero post"), 'section' => true, 'section_title' => _('Vetrina post (showcase view)')],
    'showcase_auto_start' => ['default' => 0, 'label' => _("Animazione automatica"),],
    'showcase_auto_interval' => ['default' => 5000, 'label' => _("Intervallo animazione automatica (ms)"),],
    'evidence_number' => ['default' => 3, 'label'=>_("Numero post"), 'section'=>true, 'section_title'=>_('Post in evidenza')],
    'evidence_auto_start' => ['default' => 0, 'label'=>_("Animazione automatica"),],
    'evidence_auto_interval' => ['default' => 5000, 'label'=>_("Intervallo animazione automatica (ms)"),],
    'image_width' => ['default' => 600, 'label'=>_("Larghezza massima immagini"), 'section'=>true, 'section_title'=>_('Media')],
    'newsletter_post_number' => [
        'default' => 10,
        'label'=>_("Numero post esportati nella lista"),
        'section'=>true,
        'section_title'=>_('Newsletter'),
        'section_description'=> $newsletter_module
        ? "<p>"._('La classe si interfaccia al modulo newsletter di gino installato sul sistema')."</p>"
        : "<p>"._('Il modulo newsletter non è installato')."</p>",
    ],
];
