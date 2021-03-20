<?php 
namespace Gino\App\Post;

use function Gino\url_instance, Gino\url_alias, Gino\url_pattern;

$post_urls = [
    url_pattern('article/detail/<slug>/', 'articolo/dettaglio/<slug>/'),
    url_instance('article/archive', 'articoli/elenco'),
];

?>