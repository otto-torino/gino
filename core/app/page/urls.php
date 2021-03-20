<?php 
namespace Gino\App\Post;

use function Gino\url_instance, Gino\url_alias, Gino\url_pattern;

$page_urls = [
    url_pattern('page/view/<slug>/', 'pagine/dettaglio/<slug>/'),
    url_instance('page/instance', 'pagine/ultime'),
    url_alias('page/view/documentazione', 'doc'),
];

?>