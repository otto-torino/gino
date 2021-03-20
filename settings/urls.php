<?php 

// APP_DIR: Percorso assoluto alla directory che contiene le app
// CORE_APP_DIR: Directory che contiene le app di sistema

// il nome della variabile di tipo array dell'applicazione deve essere : NOMEAPP + _urls
// @example page_urls

include_once APP_DIR.'/post/urls.php';
include_once CORE_APP_DIR.'/page/urls.php';

$rewritten_urls = [
    $post_urls,
    $page_urls,
];

/*
 * ##DESCRIZIONE
 * È possibile definire degli alias agli indirizzi delle risorse. Le tipologie di alias sono tre: \n
 * 1. ridefinizione dei nomi di istanza e metodo, ad esempio "prenotazioni/elenco-referente" al posto di "booking/referentlist"
 * 2. ridefinizione di un indirizzo del tipo "istanza/metodo/id" in una unica stringa, ad esempio "pagina-di-prova"
 * al posto di "page/view/contacts". In questo caso viene reimpostata la proprietà Gino.HTTP.Request::GET con il parametro 'id'.
 * 3. ridefinizione di un indirizzo con SLUG; in questo caso vengono ridefiniti i nomi di istanza e metodo, lo SLUG non cambia..
 *
 * Nei nomi degli alias non è possibile utilizzare il carattere della costante URL_SEPARATOR.
 */

?>