<?php 
namespace Gino\App\Calendar;

$search_fields = [
    'category' => array(
        'label' => _('Categoria'),
        'input' => 'select',
        'data' => Category::getForSelect($this),
        'type' => 'int',
        'options' => null
    ),
    'text' => array(
        'label' => _('Titolo/Testo'),
        'input' => 'text',
        'type' => 'string',
        'options' => null
    ),
    'date_from' => array(
        'label' => _('Da'),
        'input' => 'date',
        'type' => 'string',
        'options' => null
    ),
    'date_to' => array(
        'label' => _('A'),
        'input' => 'date',
        'type' => 'string',
        'options' => null
    ),
];

?>