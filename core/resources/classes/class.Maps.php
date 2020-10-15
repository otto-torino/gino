<?php
/**
 * @file class.Javascript.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Maps
 */
namespace Gino;

/**
 * @brief Contiene i metodi per utilizzare le mappe di OpenStreetMap o Gmaps
 * 
 * Per l'implementazione di mappe OpenStreetMap utilizzo la libreria JavaScript Leaflet che permette di 
 * sviluppare mappe geografiche interattive (WebGIS).
 * Leaflet permette di mostrare punti di interesse, linee o aree, o strutture dati come file GeoJSON, 
 * o livelli interattivi, su una mappa a tasselli.
 * 
 * # DISEGNO DI ELEMENTI SU UNA MAPPA
 * 
 * @code
 * \Gino\Loader::import('class', '\Gino\Maps');
 * $maps = new \Gino\Maps('gmaps');
 * 
 * $add_cell = array(
 *   'lat' => array(
 *     'name' => _('geolocalization'),
 *     'field' => $maps->drawGeoField()
 *   )
 * );
 * @endcode
 * 
 * # VISUALIZZAZIONE DI UN PUNTO
 * 
 * Nel metodo che gestisce il dettaglio di un elemento:
 * @code
 * // Load api
 * \Gino\Loader::import('class', '\Gino\Maps');
 * $maps = new \Gino\Maps();    // or new \Gino\Maps('gmaps')
 * $maps->loadApi();
 * 
 * // in the dictionary view
 * $dict = array(
 *   ...
 *   'mapjsscript' => $maps->scriptToShowPoint($item->lng.', '.$item->lat, ['popupContent' => $item->ml('name')])
 * }
 * @endcode
 * 
 * Nella vista:
 * @code
 * <? if($item->lng and $item->lat): ?>
 *   <div id="map-canvas" style="width: 100%; height: 200px; margin-top: 20px; margin-bottom: 20px;"></div>
 *   <?= $mapjsscript ?>
 * <? endif ?>
 * @endcode
 * 
 * # LIBRERIE ALTIMETRIA
 * 
 * Sono disponibili due librerie Leaflet che utilizzano d3:
 * - Raruto/leaflet-elevation (@link https://github.com/Raruto/leaflet-elevation)
 * - MrMufflon/Leaflet.Elevation (@link https://github.com/MrMufflon/Leaflet.Elevation)
 * 
 * Con le attuali librerie leaflet, agendo sul profilo altimetrico, la libreria di MrMufflon 
 * non mostra la corrispondenza sui punti del percorso.
 * Per creare il profilo altimetrico di un percorso bisogna creare una unica geometry LineString.
 */
class Maps {
    
    private $_api;
    private $_registry;
    private static $valid_api = ['osm', 'gmaps'];
    
    public function __construct($api='osm') {
        
        if(!in_array($api, self::$valid_api)) {
            throw new \Gino\Exception\Exception500();
        }
        
        $this->_api = $api;
        $this->_registry = \Gino\Registry::instance();
    }
    
    /**
     * @brief Carica le librerie delle mappe prescelte
     * 
     * @param array $plugins elenco dei plugin aggiuntivi da caricare
     * @return void
     */
    public function loadApi($plugins=[]) {
        
        if($this->_api == 'osm') {
            $this->osmAPI($plugins);
        }
        else {
            $this->gmapsApi($plugins);
        }
    }
    
    /**
     * @brief Script javascript per la visualizzazione di un punto nella mappa
     * 
     * @param string $coordinates
     * @param array $options
     * @return string
     */
    public function scriptToShowPoint($coordinates, $options=[]) {
        
        if($this->_api == 'osm') {
            return $this->osmShowPoint($coordinates, $options);
        }
        else {
            return $this->gmapsShowPoint($coordinates, $options);
        }
    }
    
    /**
     * @brief API per visualizzare una mappa OpenStreetMap
     * 
     * @param array $plugins elenco dei plugin aggiuntivi da caricare
     *   - @b fullscreen: default true
     *   - @b search: default false
     *   - @b elevation: default false (libreria Raruto/leaflet-elevation)
     *   - @b elevation-mm: default false (libreria MrMufflon/Leaflet.Elevation)
     * @return NULL
     */
    private function osmAPI($plugins) {
        
        $fullscreen = \Gino\gOpt('fullscreen', $plugins, true);
        $search = \Gino\gOpt('search', $plugins, false);
        $elevation = \Gino\gOpt('elevation', $plugins, false);
        $elevation_mm = \Gino\gOpt('elevation-mm', $plugins, false);
        
        $this->_registry->addRawJs("
    	    <script src=\"https://unpkg.com/leaflet@1.5.1/dist/leaflet.js\"
    	    integrity=\"sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og==\"
    	    crossorigin=\"\"></script>"
        );
        $this->_registry->addRawCss("
    	    <link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.5.1/dist/leaflet.css\"
    	    integrity=\"sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==\"
    	    crossorigin=\"\"/>"
        );
        
        if($fullscreen) {
            $this->_registry->addCustomJs(SITE_LIBRARIES.'/Maps/OpenStreetMap/leaflet-fullscreen/dist/Leaflet.fullscreen.min.js',
                array('compress'=>false, 'minify'=>false));
            $this->_registry->addCss(SITE_LIBRARIES.'/Maps/OpenStreetMap/leaflet-fullscreen/dist/leaflet.fullscreen.css');
        }
        
        if($search) {
            $this->_registry->addCustomJs(SITE_LIBRARIES.'/Maps/OpenStreetMap/leaflet-search/src/leaflet-search.js', array('compress'=>false, 'minify'=>false));
            $this->_registry->addCss(SITE_LIBRARIES.'/Maps/OpenStreetMap/leaflet-search/src/leaflet-search.css');
        }
        
        if($elevation) {
            // D3 Resources
            $this->_registry->addRawJs("<script src=\"https://unpkg.com/d3@4.13.0/build/d3.min.js\" charset=\"utf-8\"></script>");
            // leaflet-gpx
            //$this->_registry->addRawJs("<script src=\"https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.4.0/gpx.js\" charset=\"utf-8\"></script>");
            
            $this->_registry->addRawJs("<script src=\"https://unpkg.com/@raruto/leaflet-elevation@0.4.1/leaflet-elevation.min.js\"></script>");
            $this->_registry->addRawCss("<link rel=\"stylesheet\" href=\"https://unpkg.com/@raruto/leaflet-elevation@0.4.1/leaflet-elevation.min.css\" />");
        }
        
        if($elevation_mm) {
            // D3 Resources
            $this->_registry->addRawJs("<script src=\"https://d3js.org/d3.v3.min.js\" charset=\"utf-8\"></script>");
            $this->_registry->addRawJs("<script src=\"https://d3js.org/queue.v1.min.js\"></script>");
            
            $this->_registry->addCustomJs(SITE_LIBRARIES.'/Maps/OpenStreetMap/leaflet-elevation-mm/dist/leaflet.elevation-0.0.4.min.js', array('compress'=>false, 'minify'=>false));
            $this->_registry->addCss(SITE_LIBRARIES.'/Maps/OpenStreetMap/leaflet-elevation-mm/dist/leaflet.elevation-0.0.4.css');
        }
        
        return null;
    }
    
    /**
     * @brief API per visualizzare una mappa Google Maps
     * 
     * @param array $plugins elenco dei plugin aggiuntivi da caricare
     * @return NULL
     */
    private function gmapsApi($plugins) {
        
        $this->_registry->addCustomJs("https://maps.googleapis.com/maps/api/js?key=".GOOGLE_MAPS_KEY, 
            array('compress' => false, 'minify' => false));
        return null;
    }
    
    /**
     * @brief Script per la visualizzazione di un marker nella mappa di OpenStreetMap
     * 
     * @param string $coordinates coordinates in the format: **lng_value, lat_value**
     * @param array $options array associativo di opzioni
     *   - @b map_id (string): default @a map-canvas
     *   - @b popupContent (string)
     * @return string
     */
    private static function osmShowPoint($coordinates, $options=[]) {
        
        $map_id = \Gino\gOpt('map_id', $options, 'map-canvas');
        $popupContent = \Gino\gOpt('popupContent', $options, null);
        $zoom = \Gino\gOpt('zoom', $options, 13);
        
        if($popupContent) {
            $popupContent = \Gino\jsVar($popupContent);
        }
        
        $buffer = "
        
        <script type=\"text/javascript\">
        var data = [
            {
                \"type\": \"Feature\",
                \"properties\": {
                    \"name\": \"\",
                    \"popupContent\": \"$popupContent\"
                },
                \"geometry\": {
                    \"type\": \"Point\",
                    \"coordinates\": [$coordinates]
                }
            }
        ]
    
        var map = L.map('$map_id', {
            fullscreenControl: {
                pseudoFullscreen: false
            }
        }).setView([$coordinates], $zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
        }).addTo(map);

        function onEachFeature(feature, layer) {
            if (feature.properties && feature.properties.popupContent) {
                layer.bindPopup(feature.properties.popupContent);
            }
        }

        var geojsonLayer = L.geoJSON(data, {
            onEachFeature: onEachFeature
        }).addTo(map);
        
        map.fitBounds(geojsonLayer.getBounds());
        </script>";
        
        return $buffer;
    }
    
    /**
     * @brief Script per la visualizzazione di un marker nella mappa di Google Maps
     * 
     * @param string $coordinates coordinates in the format: **'lat_value', 'lng_value'**
     * @param array $options array associativo di opzioni
     *   - @b map_id (string): default @a map-canvas
     *   - @b popupContent (string)
     * @return string
     */
    private function gmapsShowPoint($coordinates, $options=[]) {
        
        $map_id = \Gino\gOpt('map_id', $options, 'map-canvas');
        $zoom = \Gino\gOpt('zoom', $options, 12);
        
        $buffer = "
        
        <script>
        options = {
            center: new google.maps.LatLng($coordinates),
            zoom: $zoom
        };
        var map = new google.maps.Map(document.getElementById('$map_id'), options);
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng($coordinates),
            map: map
        });
        </script>";
        
        return $buffer;   
    }
    
    /**
     * Metodi per disegnare su una mappa
     */
    
    public function drawGeoField($options=[]) {
        
        if($this->_api == 'osm') {
            return $this->osmDrawer($options);
        }
        else {
            return $this->gmapsDrawer($options);
        }
    }
    
    private function osmDrawer($options) {
        
        $this->_registry->addRawJs("<script type=\"text/javascript\">window.OSM_TOKEN = '".OPENSTREETMAP_KEY."';</script>");
        $this->_registry->addRawJs("<script type=\"text/javascript\" src=\"".SITE_LIBRARIES."/Maps/osmdrawer/dist/osmdrawer.min.js\"></script>");
        $this->_registry->addRawJs("<script type=\"text/javascript\" src=\"".SITE_LIBRARIES."/Maps/osm.js\"></script>");
        
        return "<div id=\"map-canvas\" style=\"max-width: 100%; width: 1000px; height: 400px; margin: auto;\"></div>";
        
        /*
         <h2>Mappa geolocalizzazione evento</h2>
         <ul><li>Clicca sulla mappa per inserire il marker, oppure scrivi l'indirizzo esatto nel campo di testo nel menu e poi premi il bottone con il mirino.</li>
         <li>Quando il marker è posizionato clicca l'icona di esportazione.</li></ul>
         */
    }
    
    private function gmapsDrawer($options) {
        
        $buffer = self::scriptConvertAddress($options);
        $buffer .= self::inputConvertAddress($options);
        return $buffer;
    }
    
    /**
     * @brief Include il file javascript con la libreria delle mappe
     * @return string
     */
    public static function abiMapLib() {
        
        $buffer = "<script type=\"text/javascript\" src=\"".SITE_LIBRARIES."/Maps/abiMap.js\"></script>\n";
        return $buffer;
    }
    
    /**
     * @brief Script per la conversione di un indirizzo in longitudine/latitudine
     * @param array $options
     *   - @b button_id (string): valore id del bottone (default map_coord)
     *   - @b map_id (string): valore id della mappa (default map_address)
     * @return string
     */
    private static function scriptConvertAddress($options=array()) {
        
        $button_id = gOpt('button_id', $options, 'map_coord');
        $map_id = gOpt('map_id', $options, 'map_address');
        
        $buffer = self::abiMapLib();
        $buffer .= "<script type=\"text/javascript\">";
        $buffer .= "function convert() {
			var addressConverter = new AddressToPointConverter('".$button_id."', 'lat', 'lng', $('".$map_id."').value, {'canvasPosition':'over'});
        	addressConverter.showMap();
        }\n";
        $buffer .= "</script>";
        
        return $buffer;
    }
    
    /**
     * @brief Input localizzazione
     * @param array $options
     *   - @b button_id (string): valore id del bottone (default map_coord)
     *   - @b map_id (string): valore id della mappa (default map_address)
     *   - @b map_key (string): Google Map Key
     *   - @b label (string|array): label dell'input form
     * @return string
     */
    private static function inputConvertAddress($options=array()) {
        
        $button_id = gOpt('button_id', $options, 'map_coord');
        $map_id = gOpt('map_id', $options, 'map_address');
        $map_key = gOpt('map_key', $options, GOOGLE_MAPS_KEY);
        $label = gOpt('label', $options, array(_("Indirizzo localizzazione"), _("es: torino, piazza castello<br />utilizzare 'converti' per calcolare latitudine e longitudine")));
        
        $gmk = $map_key ? "key=".$map_key."&" : '';
        
        $onclick = "onclick=\"Asset.javascript('https://maps.google.com/maps/api/js?".$gmk."callback=convert')\"";
        
        $convert_button = \Gino\Input::input($button_id, 'button', _("converti"), array("id" => $button_id, "classField" => "generic", "js" => $onclick));
        
        $input = \Gino\Input::input_label($map_id, 'text', '', $label,
            array("size" => 40, "maxlength" => 200, "id" => $map_id, "text_add" => "<p>".$convert_button."</p>"));
        
        return $input;
    }

    
}
