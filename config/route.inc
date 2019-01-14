<?php
/**
 * @file route.inc
 * @brief Contiene gli alias URL
 * 
 * @copyright 2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##DESCRIZIONE
 * È possibile definire degli alias agli indirizzi delle risorse. Le tipologie di alias sono due: \n
 * 1. ridefinizione dei nomi di istanza e metodo, ad esempio "prenotazioni/elenco-referente" al posto di "booking/referentlist"
 * 2. ridefinizione di un indirizzo del tipo "istanza/metodo/id" in una unica stringa, ad esempio "pagina-di-prova" al posto di "page/view/contacts".
 * In quest'ultimo caso viene reimpostata la proprietà Gino.HTTP.Request::GET con il parametro 'id'.
 * 
 * Nei nomi degli alias non è possibile utilizzare il carattere della costante URL_SEPARATOR.
 */

$config_url_alias = [
    
];

$config_instances_alias = [
    
];
