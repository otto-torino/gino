<?php
/**
 * @file common.inc
 * @brief 
 * 
 * @copyright 2020 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * 
 * #DESCRIZIONE
 * Includere il file ove necessario (@see \Gino\Document):
 * @code
 * require CONFIG_DIR.OS.'common.inc';
 * @endcode
 */

/**
 * @brief Impostazioni per il caricamento dei file css e javascript
 * 
 * @desc Opzioni disponibili per la chiave @a loading per i css
 *   @a css
 *   @a raw_css
 * 
 * @desc Opzioni disponibili per la chiave @a loading per i javascript
 *   @a js
 *   @a core_js
 *   @a custom_js
 * 
 * @see \Gino\Registry
 * @var array $PIPELINE
 */
$PIPELINE = [
    'stylesheets' => [
        'core' => [
            'source_filenames' => [
                SITE_JS."/bootstrap-4.1.3/css/bootstrap.min.css",
                CSS_WWW."/styles.css",
                CSS_WWW."/jquery-ui.min-1.12.1.css",
                CSS_WWW."/jquery-ui.min-1.12.1-update.css",
            ],
            //'output_filename' => 'core/css/core.min.css',
            'loading' => 'css'
        ],
    ],
    'javascripts' => [
        'core' => [
            'source_filenames' => [
                SITE_JS."/modernizr.js",
                SITE_JS."/gino-min.js",
                // jquery
                SITE_JS."/jquery/jquery-3.3.1.min.js",
                SITE_JS."/jquery/jquery-ui-1.12.1.js",
                SITE_JS."/jquery/core.js",
                // A kickass library used to manage poppers in web applications
                SITE_JS."/popper.min.js",
                SITE_JS."/bootstrap-4.1.3/js/bootstrap.min.js",
            ],
            //'output_filename' => 'core/js/core.min.js',
            'loading' => 'core_js'
        ],
    ],
    //'CSS_COMPRESSOR' => false,
    //'JS_COMPRESSOR' => false,
];

/**
 * @brief Imposta i Middleware
 * @see \Gino\Middleware\Headers
 * @var array $MIDDLEWARE
 */
$MIDDLEWARE = [
    'HEADERS' => true
];

