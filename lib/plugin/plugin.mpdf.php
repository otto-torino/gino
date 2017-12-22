<?php
/**
 * @mainpage Libreria per la generazione dei file pdf
 * 
 * Plugin per la creazione di file PDF con la libreria mPDF
 *   @link https://github.com/mpdf/mpdf
 *   @link https://mpdf.github.io/
 *   @link https://packagist.org/packages/mpdf/mpdf
 * mPDF is a PHP class to generate PDF files from HTML with Unicode/UTF-8 and CJK support.
 * 
 * ##INSTALLATION
 * ---------------
 * Official installation method is via composer and its packagist package mpdf/mpdf (@link https://packagist.org/packages/mpdf/mpdf).
 * @code
 * $ composer require mpdf/mpdf
 * @endcode
 * 
 * ###In addition
 * Oltre a queste operazioni si dovrebbe consentire la lettura/scrittura alle seguenti directory:
 * - contents/tmp/ (definita nella proprietà $_temp_dir della classe plugin_mpdf)
 * - ??? mpdf/ttfontdata/
 * - ??? mpdf/graph_cache/
 * 
 * I permessi read/write alla directory "tmp" sono necessari quando si attiva la visualizzazione della barra di progresso. \n
 * ??? I permessi read/write alla directory mpdf/ttfontdata/ sono necessari per evitare gli errori di questo tipo:
 * @code
 * file_put_contents(/.../lib/mpdf/ttfontdata/dejavusanscondensed.GSUBGPOStables.dat): failed to open stream: Permission denied ...
 * @endcode
 * 
 * ##DESCRIPTION
 * ---------------
 * Il file plugin.mpdf.php comprende due classi:
 *   - @a gino_mpdf
 *   - @a plugin_mpdf
 * 
 * ###Classe gino_mpdf
 * La classe gino_mpdf funge da interfaccia alla classe plugin_mpdf e definisce le impostazioni base del file pdf 
 * (gino_mpdf::defineBasicOptions()), l'header e il footer di default, il nome standard del file. \n
 * In pratica questa classe raggruppa i metodi di definizione dei contenuti; metodi che possono essere sovrascritti 
 * da una classe costruita appositamente per la generazione di uno specifico file pdf (ad esempio class.Pdf.php).
 * 
 * ###Classe plugin_mpdf
 * La classe plugin_mpdf costruisce il file pdf. Utilizzando il metodo plugin_mpdf::setPhpParams() è possibile impostare 
 * alcuni parametri php.
 * 
 * ###Interfacce per la generazione del pdf
 * I metodi che devono essere richiamati dalle applicazioni per generare i pdf sono:
 *   - gino_mpdf::pdfFromPage()
 *   - gino_mpdf::create()
 * dove il metodo @a gino_mpdf::pdfFromPage() viene utilizzato per generare il pdf della visualizzazione di una pagina web, 
 * mentre @a gino_mpdf::create() per generare un file con un html personalizzato.
 * 
 * ###Processo
 * gino_mpdf::create() istanzia plugin_mpdf e richiama plugin_mpdf::makeFile(); a sua volta plugin_mpdf::makeFile() 
 * istanzia \Mpdf\Mpdf.
 * 
 * MODI DI UTILIZZO
 * ---------------
 * La libreria fornisce i metodi per generare il pdf di una pagina web oppure per generare un pdf costruito appositamente 
 * (ad esempio un report).
 * 
 * ###File pdf di una pagina web
 * Per stampare a video il pdf di una pagina si aggiunge all'indirizzo il parametro "?pdf=1" e si utilizza 
 * la risposta Gino.Http.ResponsePdf
 * @code
 * $pdf = \Gino\cleanVar($request->GET, 'pdf', 'int', '');
 * if($pdf)
 * {
 *   \Gino\Loader::import('class/http', '\Gino\Http\ResponsePdf');
 *   return new \Gino\Http\ResponsePdf($render, array(
 *     'css_file'=>array('css/mpdf.css'),
 *     'filename'=>'doc.pdf'
 *   ));
 * }
 * @endcode
 * 
 * ###File pdf personalizzato
 * Creare nella classe controller il metodo pubblico che gestisce la generazione del file pdf (inserirlo nel file ini). 
 * Segue un metodo di esempio:
 * @code
 * public function createPdf() {
 *   require_once(PLUGIN_DIR.OS.'plugin.mpdf.php');
 *   // custom files...
 *   require_once 'class.Pdf.php';
 *   \Gino\Loader::import('controllername', array('PdfReport1'));
 *   \Gino\Loader::import('controllername', array('PdfReport2'));
 *   
 *   \Gino\Plugin\plugin_mpdf::setPhpParams(array('disable_error'=>false));
 *   // setPhpParams(['disable_error' => false, 'max_execution_time' => 300, 'memory_limit' => '-1'])
 *   
 *   // Opzioni per la generazione dei pdf e la definizione di valori base per mPDF
 *   $options = Pdf::defineBasicOptions();
 *   
 *   $options['debug'] = false;
 *   $options['output'] = [inline|file|download];
 *   $options['img_dir'] = 'app/appname/img';
 *   $options['save_dir'] = Pdf::pathTofile($this);
 *   $options['css_file'] = array('app/appname/css/base.css');
 *   $options['css_html'] = 'app/appname/css/base_web.css';
 *   $options['link_return'] = string;
 *   $options[...] = [...];
 *   // End
 *   
 *   $pdf = new \gino\App\Appname\controllername(array('html' => bool, [...]));
 *   return $pdf->generate($this, $options);
 * }
 * @endcode
 * 
 * Nel caso si abbia bisogno di creare più file pdf oppure anche soltanto di strutturarli in un modo più complesso può essere più efficiente creare nella classe controller 
 * un wrapper per i pdf (ad esempio createGinoPdf) che richiami una classe apposita (ad esempio GinoPdf) che estende la classe gino_mpdf. \n
 * La costruzione del singolo file sarà delegata a una apposita classe che estende, come da esempio, la classe GinoPdf. 
 * In questa classe (myGinoPdf) sarà così possibile personalizzare i metodi content, footer, header, sovrascrivendoli.
 * 
 * @code
 * ClassController::createGinoPdf() {
 *   ...
 *   plugin_mpdf::setPhpParams([...]);
 *   $obj = new myGinoPdf([...]);
 *   return $obj->generate([...]);
 * }
 * 
 * class myGinoPdf extends GinoPdf {
 *   content(), header(), footer()
 * }
 * 
 * class GinoPdf extends gino_mpdf {
 *   setFileName()
 *   generate() {
 *     $pdf = $this->create([...]);
 *     if($html)
 *       return $pdf;
 *     
 *     if($link_return)
 *       $this->redirect($link_return);
 *     return null;
 *   }
 * }
 * @endcode
 * 
 * ##HEADER/FOOTER
 * ---------------
 * L'header e il footer del pdf devono essere passati come opzioni al metodo plugin_mpdf::htmlStart(); 
 * per non stampare il footer occorre impostare il parametro @a footer a @a false. \n
 * Per visualizzare un esempio di header e footer vedere i metodi gino_mpdf::defaultHeader() e gino_mpdf::defaultFooter().
 * 
 * ##OUTPUT
 * ---------------
 * La libreria gestisce i seguenti output:
 *   - stampare a video l'html (string)
 *   - inviare il file inline al browser (inline)
 *   - salvare localmente il file (file)
 *   - far scaricare il file (download)
 *   - creare il file e inviarlo come allegato email
 * 
 * @code
 * gino_mpdf::create(array('output'=>[value]))
 * @endcode
 * 
 * ###Debug
 * Per attivare la modalità debug occorre passare l'opzione @a debug a gino_mpdf::create() o a gino_mpdf::pdfFromPage() che lo richiama.
 * @code
 * gino_mpdf::create(array('debug'=>true))
 * @endcode
 * 
 * Il debug viene poi gestito nella classe plugin_mpdf().
 * 
 * ##VARIE
 * ---------------
 * ###Memoria
 * La libreria mPDF utilizza una quantità notevole di memoria; nel caso in cui venga visualizzato un messaggio di errore 
 * di superamento del limite di memoria come ad esempio
 * @code
 * Fatal error: Allowed memory size of 134.217.728 bytes exhausted (tried to allocate 261904 bytes) in C:\inetpub\wwwroot\lib\MPDF\mpdf.php on line 22048
 * @endcode
 * 
 * occorre approntare alcuni accorgimenti elencati nella seguente pagina @link https://mpdf.github.io/troubleshooting/memory-problems.html
 * 
 * L'aumento di memoria allo script php può essere gestito a livello di: \n
 *   - file php.ini
 *   @code
 *   memory_limit = 128M
 *   @endcode
 *   - file php
 *   @code
 *   ini_set("memory_limit","128M")
 *   @endcode
 *   - virtualhost
 *   @code
 *   php_admin_value memory_limit "128M"
 *   @endcode
 * 
 * Limpostazione massima del limite di memoria per lo script php è
 * @code
 * ini_set("memory_limit","-1")
 * @endcode
 * 
 * ####Windows
 * La memoria può esaurirsi rapidamente durante l'esecuzione di PHP 5.3.x su Windows, e questo potrebbe essere dovuto da un bug nella versione di php per Windows. 
 * Uno script che esaurisce 256 MB di memoria su Windows può invece utilizzare solo 18MB quando viene eseguito su Linux. E sembra che non accada esclusivamente quando si utilizzano tabelle. \n
 * Quindi, se si utilizza solo Windows in un ambiente di prova e Linux per la produzione, si dovrebbe considerare di impostare il limite di memoria massimo su Windows.
 * 
 * ###Errori PHP
 * Un qualsiasi errore generato dallo script php (anche se soltanto un warning o un notice), blocca la generazione del pdf. 
 * In questo caso occorre inibire la stampa degli errori richiamando direttamente la funzione php:
 * @code
 * error_reporting(0);
 * @endcode
 * 
 * oppure
 * @code
 * \Gino\Plugin\plugin_mpdf::setPhpParams(array('disable_error'=>true));
 * @endcode
 * 
 * ###Progress bar
 * La progress bar non è raccomandata per un utilizzo generale ma può essere di aiuto in fase di sviluppo o nella generazione di documenti lenti. \n
 * Per impostare il valore a livello globale occorre editare il valore per @a progressBar nel file di configurazione config.php.
 * 
 * Per attivare la barra di progresso nella generazione inline di un PDF occorre assegnare i permessi 777 alla directory mpdf/tmp/, 
 * in quanto la libreria salva un file temporaneo in questa directory e poi lo mostra a video attraverso il file mpdf/includes/out.php.
 * 
 * ####Personalizzazione
 * La pagina della progress bar può essere personalizzata attraverso la definizione dell'opzione @a progbar_altHTML nel metodo plugin_mpdf::makeFile(). 
 * Ad esempio
 * @code
 * $mpdf->progbar_altHTML = '<html><body>
 * <div style="margin-top: 5em; text-align: center; font-family: Verdana; font-size: 12px;">
 * <img style="vertical-align: middle" src="img/loading.gif" /> Creating PDF file. Please wait...</div>'
 * @endcode
 * 
 * Inoltre è possibile sovrascrivere direttamente il metodo che genera la pagina della progress bar, ad esempio per personalizzarne la lingua o le stringhe. \n
 * In questo caso occorre modificare il metodo custom_mpdf::StartProgressBarOutput().
 * 
 * ##GESTIONE DEI CONTENUTI
 * ---------------
 * Il PDF può essere costruito in modo uniforme, impostando un insieme di parametri validi per tutte le pagine del documento, 
 * che avranno ad esempio gli stessi header e footer. \n
 * In alternativa è possibile gestire le pagine del documento in modo personalizzato utilizzando il metodo mPDF::AddPageByArray() 
 * che aggiunge una nuova pagin o un insieme di pagine al documento utilizzando un array di parametri opzionali (https://mpdf.github.io/reference/mpdf-functions/addpagebyarray.html).
 * 
 * ###Documento unico
 * 
 * Esempio di definizione dei contenuti:
 * @code
 * $html1 = $this->frontpage(array('title' => 'TITLE', 'img_dir' => 'path_to_dir'));
 * $html2 = $this->section01();
 * return array(
 *   array('html' => $html1, 'header_page' => null, 'footer_page' => $this->footer(), 'margin_top' => 14), 
 *   array('html' => $html2, 'header_page' => $this->header(), 'footer_page' => $this->footer(), 'margin_top' => 28)
 * );
 * @endcode
 * 
 * ###Documento personalizzato
 * Inpostando un array di pagine è possibile generare un documento totalmente personalizzato. 
 * In particolare si possono definire header e footer per singole pagine o insiemi di pagine (@a header_page, @a footer_page); 
 * inoltre si possono definire i parametri che seguono dopo l'esempio.
 * 
 * Esempio di definizione dei contenuti:
 * @code
 * $buffer = $this->frontpage(array('title' => 'TITLE', 'img_dir' => 'path_to_dir'));
 * $buffer .= $this->breakpage();
 * $buffer .= $this->section01();
 * return $buffer;
 * @endcode
 * 
 * ####orientation = L  P
 * Questo attributo specifica l'orientamento della pagina. Un valore BLANK o omesso lascia invariato l'orientamento corrente. \n
 * I valori validi sono: \n
 * L or landscape: Landscape
 * P or portrait: Portrait
 * 
 * ####resetpagenum = 1 - ∞
 * Sets/resets the document page number to $resetpagenum starting on the new page. (The value must be a positive integer). \n
 * BLANK or omitted or 0 leaves the current page number sequence unchanged.
 *
 * ####pagenumstyle = 1  A  a  I  i
 * Sets/resets the page numbering style (values as for lists). \n
 * BLANK or omitted leaves the current page number style unchanged.
 * Values (case-sensitive): \n
 * 1: Decimal - 1,2,3,4…
 * A: Alpha uppercase - A,B,C,D...
 * a: Alpha lowercase - a,b,c,d...
 * I: Roman uppercase - I, II, III, IV...
 * i: Roman lowercase - i, ii, iii, iv...
 * 
 * ####suppress = on  off  1  0
 * suppress=on will suppress document page numbers from the new page onwards (until $suppress=off is used). \n
 * BLANK or omitted leaves the current condition unchanged.
 * Values (case-insensitive): \n
 * 1 or on: Suppress (hide) page numbers from the new page forwards.
 * 0 or off: Show page numbers from the new page forwards.
 * 
 * ####margin
 * - margin_left
 * - margin_right
 * - margin_top
 * - margin_bottom
 * - margin_header
 * - margin_footer
 * 
 * Sets the page margins from the new page forwards. All values should be specified as LENGTH in millimetres. \n
 * BLANK or omitted leaves the current margin unchanged. NB “0” (zero) will set the margin to zero.
 * 
 * ###GESTIONE DELLE STRINGHE
 * Le stringhe di testo sono gestite dal metodo text() che richiama le funzioni presenti nel file func.mpdf.php. 
 * Le tipologie trattate sono: \n
 *   - @a text, richiama la funzione @a pdfChars() (default)
 *   - @a textarea, richiama la funzione @a pdfChars_Textarea()
 *   - @a editor, richiama la funzione @a pdfTextChars()
 * 
 * Nel caso in cui i dati in arrivo dal database non vengano gestiti attraverso l'interfaccia di gestione delle stringhe text(), 
 * la funzione php htmlentities() presente nelle funzioni del file func.mpdf.php (anche pdfHtmlToEntities()) 
 * potrebbe determinare la creazione di un file pdf costituito unicamente da una pagina bianca.
 * 
 * La classe @a gino_mpdf mette a disposizione il metodo mText() per interfacciarsi a plugin_mpdf::text().
 * 
 * ###BREAKPAGE
 * Occorre fare attenzione al posizionamento dei breakpage, in quanto un breakpage a fine html genera una pagina vuota.
 * 
 * La definizione dei contenuti di un pdf a partire da un array di singoli contenuti html avviene unendo questi singoli contenuti che saranno tra loro separati. 
 * In questo caso non posizionare i breakpage a fine html in quanto i singoli contenuti html vengono sempre mostrati a partire da una nuova pagina.
 * 
 * ##PERMESSI DEL FILE PDF
 * ---------------
 * L'opzione @a protection (array) permette di crittografare e impostare i permessi sul file pdf. Di default il documento non è crittografato e garantisce tutte le autorizzazioni all'utente (valore null di @a protection). Al contrario un array vuoto nega ogni autorizzazioni all'utente. \n
 * L'array può includere alcuni, tutti o nessuno dei seguenti valori che indicano i permessi concessi (@see http://mpdf1.com/manual/index.php?tid=129&searchstring=setprotection):
 *   - @a copy
 *   - @a print
 *   - @a modify
 *   - @a annot-forms
 *   - @a fill-forms
 *   - @a extract
 *   - @a assemble
 *   - @a print-highres
 * 
 * Le password dell'utente e del proprietario vengono passate attraverso le opzioni @a user_password e @a owner_password.
 * 
 * ##GESTIONE FILE CSS
 * ---------------
 * I file css possono essere caricati come opzione del metodo definePage() in due modi, come stringa o come array. 
 * Sarà poi il metodo htlmStart() a prendersi carico della corretta inclusione dei file css nel codice html dal quale verrà generato il file pdf. \n
 * 
 * 1) stringa: in questo caso viene incluso nel codice html soltanto il file css indicato, ad esempio
 * @code
 * array([...,] 'css_file'=>'app/appname/css/report.css'[, ...])
 * @endcode
 * 
 * Eventuali altri file dovranno essere inclusi utilizzando la chiave \@import nel file css
 * @code
 * @import url(test.css);
 * .void {}
 * .title { color: red; }
 * @endcode
 * In questo caso ho riscontrato che la prima classe css dopo la direttiva \@import non viene presa in considerazione, per cui è necessario inserire una classe "finta", 
 * come 'void' nell'esempio appena sopra.
 * 
 * 2) array: in questo caso vengono inclusi nel codice html tutti i file css indicati, nell'ordine degli elementi nell'array, ad esempio
 * @code
 * array([...,] 'css_file'=>array('app/appname/css/report.css', 'app/appname/css/test.css')[, ...])
 * @endcode
 * 
 * ##CSS/STILI
 * ---------------
 * ###Posizionamento
 * Gli elementi DIV possono essere posizionati staticamente nella pagina, a condizione che abbiamo come parent direttamente il BODY, 
 * ovvero che non siano all'interno di una SECTION o di un altro DIV.
 * 
 * Nel caso di <pre>position:absolute;</pre> il blocco prende come riferimento la pagina senza tenere in considerazione i margini, 
 * mentre nel caso <pre>position:relative;</pre> il blocco prende come riferimento i margini della pagina. \n
 * Seguono due esempi: nel primo caso il blocco viene posizionato al vivo in basso nella pagina, 
 * mentre nel secondo caso il blocco viene posizionato a una distanza di 30mm dal basso e dentro i margini della pagina.
 * 
 * @code
 * .myfixed1 {
 *   position: absolute;
 *   overflow: visible;
 *   left: 0;
 *   bottom: 0;
 *   border: 1px solid #880000;
 *   background-color: #FFEEDD;
 *   background-gradient: linear #dec7cd #fff0f2 0 1 0 0.5;
 *   padding: 1.5em;
 *   margin: 0;
 *   font-family:sans;
 * }
 * 
 * .myfixed2 {
 *   position: fixed;
 *   overflow: auto;
 *   left: 120mm;
 *   right: 0;
 *   bottom: 0mm;
 *   height: 30mm;
 *   border: 1px solid #880000;
 *   background-color: #FFEEDD;
 *   background-gradient: linear #dec7cd #fff0f2 0 1 0 0.5;
 *   padding: 0.5em;
 *   margin: 0;
 *   font-family:sans;
 * }
 * @endcode
 * 
 * ###Rotazione del testo
 * Sull'intera riga di una tabella (tag tr) oppure su singole celle (tag td).
 * @code
 * <tr text-rotate="45">
 * oppure
 * <tr style="text-rotate: 45">
 * @endcode
 * 
 * ###Tabelle
 * Block-level tags (DIV, P etc) are ignored inside tables, including any CSS styles - inline CSS or stylesheet classes, id etc. \n
 * To set text characteristics within a table/cell, either define the CSS for the table/cell, or use in-line tags e.g. <SPAN style="...">.
 * 
 * ####font-size
 * mPDF uses autosizing tables and this influences, among other things, the font-size as well. When outputting tables with mPDF you need to set:
 * @code
 * <table style="overflow: wrap">
 * @endcode
 * on every table.
 * 
 * ####Ripetizione di header e/o footer di una tabella al cambio di pagina
 * Se una tabella è splittata su più pagine, la prima riga di una tabella sarà ripetuta in testa alla nuova pagina se:
 * @code
 * <table repeat_header="1"> o
 * <thead> o <tfoot> sono definiti
 * @endcode
 * 
 * ####Inserimento di un bordo all'inizio e alla fine di una tabella
 * @code
 * .table_style {
 *   topntail: 0.02cm solid #666;
 * }
 * @endcode
 * 
 * ####Esempi di celle di tabella
 * @code
 * <td colspan="2" valign="top" align="center">text_label:<br />text_value</td>
 * <td width="50%" valign="top" rowspan="2">text_label:<br />text_value</td>
 * @endcode
 * 
 * ###Cambiare le dimensioni della pagina nel documento
 * L'esempio seguente stampa una pagina A4 (landscape).
 * 
 * Come css
 * @code
 * .headerPagedStart { page: smallsquare; }
 * .headerPagedEnd { page: standard; }
 * @page smallsquare {
 *   sheet-size: A4-L; // width height <length>{2} | Letter | Legal | Executive | A4 | A4-L | A3 | A3-L etc. Any of the standard sheet sizes can be used with the suffix '-L' for landscape
 *   size: 15cm 20cm; // width height <length>{1,2} | auto | portrait | landscape NB 'em' and 'ex' % are not allowed
 *   margin: 5%;
 *   margin-header: 5mm;
 *   margin-footer: 5mm;
 * }
 * @page standard {
 *   sheet-size: A4; margin: 15mm; margin-header: 5mm; margin-footer: 5mm;
 * }
 * @endcode
 * 
 * Nel codice html
 * @code
 * <h2 class="headerPagedStart">Paged Media using CSS</h2>
 * <h4>Changing page (sheet) sizes within the document</h4> <p>This should print on an A4 (landscape) sheet</p> <p>Nulla felis erat, imperdiet eu, ..........</p>
 * <div class="headerPagedEnd"></div>
 * @endcode
 * 
 * ##GESTIONE DEI CONTENUTI IN TABELLA O IN SEQUENZA
 * ---------------
 * La classe @a gino_mpdf mette a disposizione i seguenti metodi per gestire i contenuti in sequenza o in formato tabellare, 
 * dall'alto verso il basso a partire da sinistra verso destra: \n
 * - @a buildColumnsGivenRows(), costruisce una o più pagine incolonnando gli elementi per un numero massimo di righe
 * - @a buildTablesGivenRows(), costruisce una o più tabelle (una per pagina) degli elementi incolonnandoli per un numero massimo di righe
 * - @a buildTablesGivenItems(), costruisce una singola tabella incolonnando gli elementi
 * 
 * Il metodo @a arrangeItems() permette di riorganizzare gli elementi da mostrare attraverso il metodo @a buildColumnsGivenRows() 
 * aggiungendone alla fine delle colonne di pagina prima di elementi opportunamente contrassegnati da una chiave (@a code).
 * È possibile aggiungere al massimo tre elementi consecutivi.
 * 
 * Seguono due esempi di utilizzo
 * @code
 * $items[] = $content_title;
 * $items[] = $this->cardInTable($product);
 * 
 * $buffer .= $this->buildColumnsGivenRows($items, array(
 *   'cols' => 2,
 *   'max_rows' => 6,
 *   'css_div' => 'list_items',
 *   'css_columns' => array('column1' => 'col1', 'column2' => 'col2'),
 * ));
 * @endcode
 * 
 * @code
 * $items[] = array(
 *   'custom_table' => 'titles', 
 *   'custom_td' => 'release', 
 *   'custom_data' => $content
 * );
 * $items[] = $this->cardInTable($product);
 * 
 * $buffer .= $this->buildTableFromRows($items, array(
 *   'cols' => 2, 
 *   'max_rows' => 40, 
 *   'header_tr' => 'header_table', 
 *   'header_th' => array('col1', 'col2')
 * ));
 * @endcode
 */

/**
 * @file plugin.mpdf.php
 * @brief Contiene le classi gino_mpdf, plugin_mpdf
 * 
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin
 * @description Namespace che comprende classi di tipo plugin
 */
namespace Gino\Plugin;

require_once SITE_ROOT . '/vendor/autoload.php';
require_once LIB_DIR.OS."func.mpdf.php";

/**
 * @brief Classe che funge da interfaccia alla classe plugin_mpdf
 * 
 * @copyright 2014-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * I metodi header(), footer() e content() contengono i dati del pdf e vengono sovrascritti dalla child class. \n
 * I defaultHeader() e defaultFooter() contengono l'header e il footer di default.
 * 
 * I dati in arrivo dal database devono essere gestiti attraverso l'interfaccia di gestione delle stringhe gino_mpdf::mText().
 */
class gino_mpdf {
	
	protected $_registry;
	
	/**
	 * Indica se mostrare l'html
	 * @var boolean
	 */
	protected $_html;
	
	/**
	 * Oggetto pdf
	 * @var object
	 */
	protected $_pdf;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b html (boolean): indica se mostrare l'html o creare il file pdf
	 * @return void
	 * 
	 * Se si mostra l'html (html=true) la pagina carica lo stesso il file di stile specificato nell'opzione @a css_file del metodo create().
	 */
	function __construct($options=array()) {
		
		$this->_html = \Gino\gOpt('html', $options, false);
		
		$this->_registry = \Gino\registry::instance();
		$this->_pdf = null;
	}
	
	/**
	 * @brief Definisce le impostazioni base per la libreria mPDF
	 * 
	 * @param array $opt array associativo di opzioni
	 * @return array
	 */
	public static function defineBasicOptions($opt=array()) {
		
		$options = array(
			'debug' => false, 
			'css_file' => array('css/mpdf.css'), 
			'title' => 'Pdf document', 
			'author' => 'Otto Srl', 
			'creator' => 'Marco Guidotti', 
			'landscape' => false, 
			'margin_top' => 20, 
			'margin_bottom' => 30, 
			'progressBar' => false, 
			'progbar_heading'=> _("Generazione pdf - Stato di avanzamento"), 
		);
		
		return $options;
	}
	
	/**
	 * @brief Header del file pdf
	 * 
	 * @param array $options array associativo di opzioni richiamate nell'header, tra le quali
	 *   - @b img_dir (string): percorso della directory delle immagini
	 * @return string
	 */
	public function header($options=array()) {
		
		return null;
	}
	
	/**
	 * @brief Footer del file pdf
	 * 
	 * @param array $options array associativo di opzioni richiamate nell'header, tra le quali
	 *   - @b img_dir (string): percorso della directory delle immagini
	 * @return string
	 */
	public function footer($options=array()) {
		
		return null;
	}
	
	/**
	 * Definizione dei contenuti di un pdf
	 * 
	 * @param array $options array associativo di opzioni per la generazione del pdf (@see create())
	 *    - @b img_dir (string): percorso della directory delle immagini (es. app/blog/img)
	 * @return string or array
	 */
	public function content($options=array()) {
		
		return null;
	}
	
	/**
	 * @brief Header base
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b header_left (string): testo da mostrare nella parte sinistra dell'intestazione
	 *   - @b header_center (string): testo da mostrare nella parte centrale dell'intestazione
	 *   - @b title (string): titolo da mostrare sotto l'intestazione
	 * @return string
	 */
	protected function defaultHeader($options=array()) {
		
		$header_left = \Gino\gOpt('header_left', $options, null);
		$header_right = \Gino\gOpt('header_right', $options, null);
		$header_center = \Gino\gOpt('header_center', $options, null);
		$title = \Gino\gOpt('title', $options, null);
		
		$header = "<table width=\"100%\"><tr>
		<td style=\"font-size: 8pt; text-align:left;\">".$this->mText($header_left)."</td>
		<td style=\"font-size: 8pt; text-center;\">".$this->mText($header_center)."</td>
		<td style=\"font-size: 8pt; text-align:right;\">".$this->mText($header_right)."</td>
		</tr></table>";
		
		if($title) {
			$header .= "<div class=\"title\">$title</div>";
		}
		
		return $header;
	}
	
	/**
	 * @brief Footer base
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b footer_left (string): testo da mostrare nella parte sinistra del piè di pagina
	 *   - @b footer_center (string): testo da mostrare al centro del piè di pagina
	 * @return string
	 */
	protected function defaultFooter($options=array()) {
		
		$footer_left = \Gino\gOpt('footer_left', $options, null);
		$footer_center = \Gino\gOpt('footer_center', $options, null);
		
		$footer = "<div style=\"border-top:1px solid #666; padding-top:3mm;\">";
		$footer .= "<table width=\"100%\"><tr>";
		
		$width_sx = $footer_center ? 20 : 80;
		
		$footer .= "<td width=\"".$width_sx."%\" style=\"text-align:left; font-size:6pt;\">$footer_left</td>";
		if($footer_center) {
			$footer .= "<td width=\"60%\" style=\"text-align:center; font-size:6pt;\">$footer_center</td>";
		}
		
		$footer .= "<td width=\"20%\" style=\"text-align:right; font-size:6pt;\">"._("Pagina")." _NUMPAGE_ "._("di")." _TOTPAGE_</td>";
		$footer .= "</tr></table>";
		$footer .= "</div>";
		
		return $footer;
	}
	
	/**
	 * @brief Imposta il nome del file pdf
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b name (string): nome base del file
	 *   - @b date (boolean): indica se mostrare la data di creazione del file (default false)
	 * @return string
	 */
	protected function setFileName($options=array()) {
		
		$name = \Gino\gOpt('name', $options, 'doc');
		$date = \Gino\gOpt('date', $options, false);
		
		if($date)
		{
			$date = date("Ymd");
			$name .= '-'.$date;
		}
		$name .= '.pdf';
		
		return $name;
	}
	
	/**
	 * @brief Pagina di copertina
	 *
	 * @param array $options array associativo di opzioni
	 *   - @b title (string): titolo della copertina
	 * @return string
	 */
	protected function frontpage($options=array()) {
	
		$title = \Gino\gOpt('title', $options, null);
		
		$buffer = "<section>";
		if($title) {
			$buffer .= "<div class=\"cover\">".$this->mText($title)."</div>";
		}
		$buffer .= "</section>";
	
		return $buffer;
	}
	
	/**
	 * Gestisce la ripetizione di una stringa (uno o più caratteri)
	 * 
	 * @param string $string carattere/i da ripetere
	 * @param integer $num numero di ripetizioni
	 * @param integer $break numero di caratteri dopo i quali inserire un tag BR
	 * @return string
	 */
	protected function repeatChar($string, $num, $break=null) {
		
		$buffer = '';
		if($num)
		{
			$count = 1;
			for($i=1; $i<=$num; $i++)
			{
				$buffer .= $string;
				
				if($count == $break)
				{
					$buffer .= "<br />";
					$count = 1;
				}
				else $count++;
			}
		}
		else $buffer = $string;
		
		return $buffer;
	}
	
	/**
	 * Genera il pdf di una pagina html
	 * 
	 * @see Gino.Plugin.gino_mpdf::defineBasicOptions()
	 * @see Gino.Plugin.gino_mpdf::create()
	 * @param string $content contenuto della risposta
	 * @param array $opts
	 *   array associativo di opzioni
	 *   - @b link_return (string): indirizzo di reindirizzamento dopo la creazione del file pdf
	 *   opzioni di gino_mpdf::create()
	 *   - @b output (string): tipo di output (default inline)
	 *   - @b filename (string): nome del file
	 *   - @b img_dir (string): percorso della directory delle immagini per header e footer (es. app/blog/img)
	 *   - @b save_dir (string): percorso della directory di salvataggio dei file (es. $this->getBaseAbsPath().'/pdf')
	 *   - @b css_html (string): file css per l'html (es. app/blog/blog_blog.css)
	 *   opzione del costruttore della classe plugin_mpdf
	 *   - @b output (string): tipo di output (default inline)
	 *   - @b debug (boolean): abilita la modalità debug
	 *   opzioni di plugin_mpdf::makeFile()
	 *   opzioni di plugin_mpdf::definePage()
	 *   - @b css_file (mixed): file css per per il pdf (es. array('app/blog/pdf.css', 'css/mpdf.css'))
	 *   - @b header
	 *   - @b footer
	 *   - @b debug_exit
	 * @return mixed (void or string)
	 */
	public function pdfFromPage($content, $opts=array()) {
		
		$link_return = \Gino\gOpt('link_return', $opts, null);
		$output = \Gino\gOpt('output', $opts, 'inline');
		$debug = \Gino\gOpt('debug', $opts, null);
		
		$css_file = \Gino\gOpt('css_file', $opts, null);
		$css_html = \Gino\gOpt('css_html', $opts, null);
		$img_dir = \Gino\gOpt('img_dir', $opts, null);
		$save_dir = \Gino\gOpt('save_dir', $opts, null);
		$filename = \Gino\gOpt('filename', $opts, null);
		
		// Def options
		$options = gino_mpdf::defineBasicOptions();
        
        $options['output'] = $output;
        if(is_bool($debug)) $options['debug'] = $debug;
        
        if($css_file) $options['css_file'] = $css_file;
        if($css_html) $options['css_html'] = $css_html;
        if($img_dir) $options['img_dir'] = $img_dir;
        if($save_dir) $options['save_dir'] = $save_dir;
        if($filename) $options['filename'] = $filename;
        
		$options['content'] = \Gino\htmlToPdf($content);
		// /Def
		
		$pdf = $this->create($options);
		
		if($this->_html) {
			return $pdf;
		}
		
		if($link_return) {
			$this->redirect($link_return);
		}
		return null;
	}
	
	/**
	 * @brief Costruisce il file
	 * 
	 * @see Gino.Plugin.plugin_mpdf::definePage()
	 * @see Gino.Plugin.plugin_mpdf::makeFile()
	 * @see header()
	 * @see footer()
	 * @see content()
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b output (string): tipologia di output del pdf (@see plugin_mpdf::outputs())
	 *     - @a file
	 *     - @a inline
	 *     - @a download
	 *     - @a string
	 *   - @b debug (boolean): attiva il debug (default false)
	 *   - @b content (string): contenuto del file; se nullo va a leggere il metodo self::content()
	 *   - @b filename (string): nome del file (default doc.pdf)
	 *   - @b img_dir (string): percorso della directory delle immagini nel pdf
	 *   - @b save_dir (string): percorso della directory di salvataggio del file
	 *   - @b css_file (mixed): percorso ai file css inclusi nel pdf (caricati in @see plugin_mpdf::definePage())
	 *   - @b css_html (string): percorso al file css incluso nel formato html (ad esempio 'app/news/css/web.css')
	 *   opzioni specifiche del metodo plugin_mpdf::makeFile():
	 *   - @b title (string)
	 *   - @b author (string)
	 *   - @b creator (string)
	 *   - @b format (string)
	 *   - @b landscape (boolean)
	 *   - @b protection (array)
	 *   - @b user_password (string)
	 *   - @b owner_password (string)
	 *   - @b title (string)
	 *   - @b watermark (boolean)
	 *   - @b watermark_text (string)
	 *   - @b margin_top (integer)
	 *   - @b margin_bottom (integer)
	 *   - @b margin_header (integer)
	 *   - @b margin_footer (integer)
	 *   - @b simpleTables (boolean)
	 *   - @b showStats (boolean)
	 *   - @b progressBar (mixed)
	 *   - @b progbar_heading (string)
	 *   - @b progbar_altHTML (string)
	 *   opzioni specifiche del metodo plugin_mpdf::definePage():
	 *   - @b header (string)
	 *   - @b footer (string)
	 *   - @b debug_exit (boolean)
	 * @return mixed (@see plugin_mpdf::makeFile())
	 *   - string, html and output string
	 *   - boolean true, output File
	 *   - exit, output inline and download
	 */
	public function create($options=array()) {
		
		$output = array_key_exists('output', $options) ? $options['output'] : null;
		$debug = array_key_exists('debug', $options) ? $options['debug'] : false;
		
		$filename = \Gino\gOpt('filename', $options, 'doc.pdf');
		$img_dir = \Gino\gOpt('img_dir', $options, null);
		$save_dir = \Gino\gOpt('save_dir', $options, '');
		$css_html = \Gino\gOpt('css_html', $options, null);
		
		$save_dir = (substr($save_dir, -1) != '/' && $save_dir != '') ? $save_dir.'/' : $save_dir;
		
		$pdf = new plugin_mpdf(
			array(
				'output'=>$output, 
				'debug'=>$debug
			)
		);
		
		$this->_pdf = $pdf;
		$options['object'] = $this;
		
		// Html
		if($this->_html)
		{
			$content = \Gino\gOpt('content', $options, null);
			if(!$content) {
				$content = $this->content($options);
			}
			
			if(is_array($content)) {
				$content = implode("<br />", $content);
			}
			
			if($css_html) {
				$this->_registry->addCss($css_html);
			}
			
			return $content;
		}
		// /Html
		
		if($output == 'file')
		{
			if(!is_dir($save_dir)) {
				mkdir($save_dir, 0777, true);
			}
			$file = $save_dir.$filename;
		}
		else {
			$file = $filename;
		}
		
		$res = $pdf->makeFile($file, $options);
		return $res;
	}
	
	/**
	 * Redirige il processo di creazione del file all'indirizzo specificato
	 * 
	 * Si utilizza il javascript perché la funzione header() ritorna l'errore: \n
	 * Warning: Cannot modify header information - headers already sent in ...
	 * 
	 * @param string $link
	 */
	protected function redirect($link) {
		
		echo "<script type=\"text/javascript\">window.location.href='".$link."';</script>";
		exit();
	}
	
	/**
	 * Interfaccia di gestione delle stringhe
	 * 
	 * Se esiste l'oggetto pdf, le stringhe vengono passate al metodo plugin_mpdf::text().
	 * 
	 * @see plugin_mpdf::text()
	 * @param string $string testo da gestire
	 * @param array $options array associativo di opzioni del metodo plugin_mpdf::text()
	 * @return string
	 */
	protected function mText($string, $options=array()) {
		
		if($this->_html)
		{
			$type = \Gino\gOpt('type', $options, 'text');
			
			if($type == 'textarea') {
				return \Gino\htmlCharsText($string);
			}
			elseif($type == 'editor') {
				return \Gino\htmlChars($string);
			}
			else {
				return \Gino\htmlChars($string);
			}
		}
		else {
		    return $this->_pdf->text($string, $options);
		}
	}
	
	/**
	 * @brief Interfaccia al metodo di generazione di un testo html compatibile con il pdf
	 * @description Da utilizzare per la gestione di pagine con impostazioni personalizzate.
	 * 
	 * @see plugin_mpdf::htmlCreate()
	 * @param string $html
	 * @param boolean $exit
	 * @return string
	 */
	protected function convertHtmlToPdf($html, $exit=true) {
		
		if($this->_html) {	
			return $html;
		}
		else {
			return $this->_pdf->htmlCreate($html, $exit);
		}
	}
	
	/**
	 * Interfaccia al metodo di break page
	 * 
	 * @see plugin_mpdf::breakPage()
	 * @return string
	 */
	protected function breakpage() {
		
		if(is_object($this->_pdf) && !$this->_html) {
			return $this->_pdf->breakpage();
		}
		else {
			return '';
		}
	}
	
	/**
	 * @brief Stampa una tabella composta da n elementi, dove ogni elemento è una riga
	 * 
	 * @param array $data elementi della tabella, ad esempio
	 * @code
	 * array(array($record1_field1, $record1_field2), array($record2_field1, $record2_field2))
	 * @endcode
	 * @param array $header intestazioni della tabella, ad esempio:
	 * @code
	 * array("<td width=\"5%\">"._("ID")."</td>", "<td width=\"10%\">"._("Quantità")."</td>")
	 * @endcode
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b class (string): nome della classe css
	 *   - @b style (string): definizione degli stili css (proprietà style)
	 *   - @b autosize (integer): fattore massimo di restringimento consentito per una singola tabella (con [autosize=1] la tabella non viene ridimensionata)
	 *   - @b border (integer): valore della proprietà border
	 * @return string
	 */
	protected function printTable($data=array(), $header=array(), $options=array()){
		
		$class = array_key_exists('class', $options) ? $options['class'] : '';
		$style = array_key_exists('style', $options) ? $options['style'] : '';
		$autosize = array_key_exists('autosize', $options) ? $options['autosize'] : 1;
		$border = array_key_exists('border', $options) ? $options['border'] : 0;
		
		if($class) $class = " class=\"$class\"";
		if($style) $style = " style=\"$style\"";
		if($autosize) $autosize = " autosize=\"$autosize\"";
		if($border) $border = " border=\"$border\"";
		
		$buffer = "<table".$autosize.$border.$style.$class.">";
		$buffer .= "<thead>";
		$buffer .= "<tr>";
		if(sizeof($header) > 0)
		{
			foreach($header AS $value)
			{
				$buffer .= $value;
			}
		}
		$buffer .= "</tr>";
		$buffer .= "</thead>";
		
		$buffer .= "<tbody>";
		if(sizeof($data) > 0)
		{
			foreach($data AS $record)
			{
				$buffer .= "<tr>";
				if(sizeof($record) > 0)
				{
					foreach($record AS $field)
					{
						$buffer .= "<td valign=\"top\">".$field."</td>";
					}
				}
				$buffer .= "</tr>";
			}
		}
		$buffer .= "</tbody>";
		$buffer .= "</table>";
		
		return $buffer;
	}
	
	/**
	 * @brief Incolonna gli elementi per un numero massimo di righe
	 *
	 * @param array $items array contenente gli elementi della tabella (@see printCell())
	 * @param array $options array associativo di opzioni
	 *   - @b cols (integer): numero di colonne (defult 2, massimo 3)
	 *   - @b max_rows (integer): numero massimo di elementi per colonna o di righe di tabella
	 * @return array(array column1, array column2, array column3)
	 */
	private function setColumnsGivenRows($items, $options=array()) {
	
		$num_cols = \Gino\gOpt('cols', $options, 2);
		$max_rows = \Gino\gOpt('max_rows', $options, null);
		
		$layout = array();
		$max_items = count($items);
		
		if($max_rows && $max_items)
		{
			// Set params
			$i = 1;
			$remaining_items = $max_items;
			$column1 = $column2 = $column3 = array();
			
			foreach($items AS $item)
			{
				if($num_cols == 1)
				{
					if($i <= $max_rows) {
						$column1[] = $item;
					}

					if($i == $max_rows or $i == $remaining_items)
					{
						$layout[] = array($column1);
						
						$i = 1;	// resets the counter of page items
						$remaining_items = $remaining_items-$max_rows;	// it subtracts the number of the items of the previous page
						$column1 = array();
					}
					else {
						$i++;
					}
				}
				elseif($num_cols == 2)
				{
					$slot2 = $max_rows*2;

					if($i <= $max_rows) {
						$column1[] = $item;
					}
					elseif($i > $max_rows && $i <= $slot2) {
						$column2[] = $item;
					}

					if($i == $slot2 or $i == $remaining_items)
					{
						$layout[] = array($column1, $column2);
						
						$i = 1;	// resets the counter of page items
						$remaining_items = $remaining_items-$slot2;	// it subtracts the number of the items of the previous page
						$column1 = $column2 = array();
					}
					else {
						$i++;
					}
				}
				elseif($num_cols == 3)
				{
					$slot2 = $max_rows*2;
					$slot3 = $max_rows*3;

					if($i <= $max_rows) {
						$column1[] = $item;
					}
					elseif($i > $max_rows && $i <= $slot2) {
						$column2[] = $item;
					}
					elseif(($i > $slot2 && $i <= $slot3)) {
						$column3[] = $item;
					}

					if($i == $slot3 or $i == $remaining_items)
					{
						$layout[] = array($column1, $column2, $column3);
						
						$i = 1;	// resets the counter of page items
						$remaining_items = $remaining_items-$slot3;	// it subtracts the number of the items of the previous page
						$column1 = $column2 = $column3 = array();
					}
					else {
						$i++;
					}
				}
			}
		}
		
		return $layout;
	}
	
	/**
	 * @brief Costruisce una o più tabelle degli elementi incolonnandoli per un numero massimo di righe
	 * 
	 * @param array $items array contenente gli elementi della tabella (@see printCell())
	 * @param array $options array associativo di opzioni
	 *   - @b cols (integer): numero di colonne (defult 2, massimo 3)
	 *   - @b max_rows (integer): numero massimo di righe della tabella
	 *   - @b css_table (string): nome della classe del tag TABLE
	 *   - @b css_td (string): nome della classe del tag TD
	 *   opzioni specifiche del metodo printThead()
	 *   - @b header_tr (string): nome della classe fel tag TR dell'intestazione di tabella
	 *   - @b $header_th (array): elenco dei nomi delle classi dei tag TH che corrispondono alle diverse colonne
	 *   opzioni specifiche del metodo printCell()
	 * @return string
	 */
	protected function buildTablesGivenRows($items, $options=array()) {
			
		$cols = \Gino\gOpt('cols', $options, 2);
		$max_rows = \Gino\gOpt('max_rows', $options, null);
		$css_table = \Gino\gOpt('css_table', $options, null);
		$header_tr = \Gino\gOpt('header_tr', $options, null);
		$header_th = \Gino\gOpt('header_th', $options, array());
		
		$options['add_td'] = true;
		
		$css_table = $css_table ? " class=\"$css_table\"" : '';
		$buffer = '';
		
		$tables = $this->setColumnsGivenRows($items, $options);
		
		if(count($tables))
		{
			$thead = $this->printThead($cols, $header_tr, $header_th);
			
			$y = 1;
			foreach ($tables AS $table)
			{
				$buffer .= "<table".$css_table.">";
				$buffer .= $thead;
				
				for($i=0; $i<=$max_rows; $i++)
				{
					$buffer .= "<tr>";
					
					if(isset($table[0][$i])) {
						$buffer .= $this->printCell($table[0][$i], $options);
					}
					if($cols > 1 && isset($table[1][$i])) {
						$buffer .= $this->printCell($table[1][$i], $options);
					}
					if($cols > 2 && isset($table[2][$i])) {
						$buffer .= $this->printCell($table[2][$i], $options);
					}
					
					$buffer .= "</tr>";
				}
				
				$buffer .= "</table>";
				if($y < count($tables)) {
					$buffer .= $this->breakpage();
				}
				else {
					$y++;
				}
			}
		}
	
		return $buffer;
	}
	
	/**
	 * @brief Costruisce una o più pagine incolonnando gli elementi per un numero massimo di righe
	 *
	 * @param array $items array contenente gli elementi della tabella (@see printCell())
	 * @param array $options array associativo di opzioni
	 *   - @b cols (integer): numero di colonne (default 2, massimo 3)
	 *   - @b max_rows (integer): numero massimo di righe per colonna
	 *   - @b css_div (string): nome della classe del tag DIV
	 *   - @b css_columns (array): elenco dei nomi delle classi dei tag DIV che identificano le colonne; sono valide le seguenti chiavi:
	 *     - @a column1, nome della classe della prima colonna
	 *     - @a column2, nome della classe della seconda colonna
	 *     - @a column3, nome della classe della terza colonna 
	 *     - @a clear, nome della classe del div che ripulisce i float
	 *   opzioni specifiche del metodo printCell()
	 *   opzioni specifiche del metodo setColumnsGivenRows()
	 *     - @b cols (integer): numero di colonne
	 *     - @b max_rows (integer): numero massimo di elementi per colonna
	 * @return string
	 */
	protected function buildColumnsGivenRows($items, $options=array()) {
		
		$css_div = \Gino\gOpt('css_div', $options, null);
		$css_columns = \Gino\gOpt('css_columns', $options, null);
		
		$options['add_td'] = false;
		
		$css_div = $css_div ? " class=\"$css_div\"" : '';
		
		$buffer = '';
		
		$columns = $this->setColumnsGivenRows($items, $options);
		
		if(count($columns))
		{
			$y = 1;
			foreach ($columns AS $column)
			{
				$buffer .= "<div".$css_div.">";
				
				if(isset($column[0]) && count($column[0]))
				{
					if($css_columns && isset($css_columns['column1'])) {
						$css = "class=\"".$css_columns['column1']."\"";
					}
					else {
						$css = "style=\"float: left;\"";
					}
					$buffer .= "<div ".$css.">";

					foreach($column[0] AS $prod) {
						$buffer .= $this->printCell($prod, $options);
					}
					$buffer .= "</div>";
				}
				if(isset($column[1]) && count($column[1]))
				{
					if($css_columns && isset($css_columns['column2'])) {
						$css = "class=\"".$css_columns['column2']."\"";
					}
					else {
						$css = "style=\"float: left;\"";
					}
					$buffer .= "<div ".$css.">";
		
					foreach($column[1] AS $prod) {
						$buffer .= $this->printCell($prod, $options);
					}
					$buffer .= "</div>";
				}
				if(isset($column[2]) && count($column[2]))
				{
					if($css_columns && isset($css_columns['column3'])) {
						$css = "class=\"".$css_columns['column3']."\"";
					}
					else {
						$css = "style=\"float: left;\"";
					}
					$buffer .= "<div ".$css.">";
		
					foreach($column[2] AS $prod) {
						$buffer .= $this->printCell($prod, $options);
					}
					$buffer .= "</div>";
				}
				
				// Clear
				if($css_columns && isset($css_columns['clear'])) {
					$css = "class=\"".$css_columns['clear']."\"";
				}
				else {
					$css = "style=\"clear: both; margin: 0pt; padding: 0pt;\"";
				}
				$buffer .= "<div ".$css."></div>";

				$y++;
				
				$buffer .= "</div>";
				if($y <= count($columns)) {
					$buffer .= $this->breakpage();
				}
			}
		}
		return $buffer;
	}
	
	/**
	 * @brief Costruisce una singola tabella degli elementi incolonnandoli dall'alto verso il basso a partire da sinistra verso destra
	 *
	 * @param array $items array contenente gli elementi della tabella (@see printCell())
	 * @param array $options array associativo di opzioni
	 *   - @b cols (integer): numero di colonne (default 2, max 3)
	 *   - @b css_table (string): nome della classe del tag TABLE
	 *   - @b css_td (string): nome della classe del tag TD
	 *   - @b add_rows (string): righe da aggiungere in calce alla tabella
	 *   opzioni del metodo printCell()
	 * @return string
	 */
	protected function buildTableGivenItems($items, $options=array()) {
	
		$cols = \Gino\gOpt('cols', $options, 2);
		$css_table = \Gino\gOpt('css_table', $options, null);
		$css_td = \Gino\gOpt('css_td', $options, null);
		$add_rows = \Gino\gOpt('add_rows', $options, null);
	
		$css_table = $css_table ? " class=\"$css_table\"" : '';
		$css_td = $css_td ? " class=\"$css_td\"" : '';
	
		$buffer = '';
	
		if(count($items))
		{
			$items_for_col = ceil(count($items)/$cols);
	
			$i = 1;
			$col1 = $col2 = $col3 = array();
			foreach($items AS $item)
			{
				if($cols == 3)
				{
					if($i <= $items_for_col) {
						$col1[] = $item;
					}
					elseif($i <= $items_for_col*2 && $i > $items_for_col) {
						$col2[] = $item;
					}
					elseif($i > $items_for_col*2) {
						$col3[] = $item;
					}
				}
				elseif($cols == 2)
				{
					if($i <= $items_for_col) {
						$col1[] = $item;
					}
					elseif($i > $items_for_col) {
						$col2[] = $item;
					}
				}
				elseif($cols == 1)
				{
					if($i <= $items_for_col) {
						$col1[] = $item;
					}
				}
				
				$i++;
			}
	
			$buffer .= "<table".$css_table.">";
	
			for($i=0, $end=$items_for_col; $i<=$end; $i++)
			{
				$buffer .= "<tr>";
	
				if(isset($col1[$i])) {
					$buffer .= $this->printCell($col1[$i], $options);
				}
				else {
					$buffer .= "<td".$css_td."></td>";
				}
	
				if(isset($col2[$i])) {
					$buffer .= $this->printCell($col2[$i], $options);
				}
				else {
					$buffer .= "<td".$css_td."></td>";
				}
	
				if(isset($col3[$i])) {
					$buffer .= $this->printCell($col3[$i], $options);
				}
				else {
					$buffer .= "<td".$css_td."></td>";
				}
				$buffer .= "</tr>";
			}
			if($add_rows) {
				$buffer .= $add_rows;
			}
	
			$buffer .= "</table>";
		}
	
		return $buffer;
	}
	
	/**
	 * @brief Riorganizza gli elementi aggiungendone alla fine delle colonne di pagina prima degli elementi identificati 
	 * dalla chiave @a code, in modo da non mostrare questi elementi come ultimi elementi
	 * @description È possibile aggiungere al massimo tre elementi consecutivi.
	 * 
	 * @param array $items elementi da riorganizzare; ogni elemento deve essere in uno dei seguenti formati:
	 *   - array(code => string[ ITEM1|ITEM2|ITEM3 ], text => string)
	 *     dove ITEM{num} indica quale elemento della sequenza si sta sostituendo (da 1 a 3)
	 *   - string
	 * @param integer $max_rows numero massimo di righe
	 * @param array $options array associativo di opzioni
	 *   - @b num_item_replace (integer): numero massimo di elementi che possono essere aggiunti in fondo alla colonna di pagina, al massimo 3 (default 1)
	 *   - @b text_replace (mixed): testo dell'elemento aggiunto (default '')
	 * @return array
	 */
	public function arrangeItems($items, $max_rows, $options=array()) {
	
		$num_item_replace = \Gino\gOpt('num_item_replace', $options, 1);
		$text_replace = \Gino\gOpt('text_replace', $options, "");
		
		$step1 = $max_rows-2;
		$step2 = $max_rows-1;
		$step3 = $max_rows;
		
		$new = array();
		
		if(count($items))
		{
			$i = 1;
			foreach ($items AS $item) {
	
				if(is_array($item)) {
					
					if($i == $step1 && $num_item_replace == 3 && $item['code'] == 'ITEM1') {
						
						$new[] = $text_replace;
						$new[] = $text_replace;
						$new[] = $text_replace;
						$new[] = $item['text'];
						$i = 1;
					}
					elseif($i == $step2 && $num_item_replace >= 2 && ($item['code'] == 'ITEM1' || $item['code'] == 'ITEM2')) {
						
						if($num_item_replace == 3) {
							$new[] = $text_replace;
						}
						
						$new[] = $text_replace;
						$new[] = $item['text'];
						$i = 1;
					}
					elseif($i == $step3 && $num_item_replace >= 1 && ($item['code'] == 'ITEM1' || $item['code'] == 'ITEM2' || $item['code'] == 'ITEM3')) {
						$new[] = $text_replace;
						$new[] = $item['text'];
						$i = 1;
					}
					else {
						$new[] = $item['text'];
					}
				}
				else {
					$new[] = $item;
				}
	
				if($i == $max_rows) {
					$i = 1;
				}
				else {
					$i++;
				}
			}
		}
		return $new;
	}

	/**
	 * @brief Costruisce l'intestazione della tabella
	 *
	 * @param integer $cols numero di colonne della tabella
	 * @param string $header_tr nome della classe fel tag TR dell'intestazione di tabella
	 * @param array $header_th elenco dei nomi delle classi dei tag TH che corrispondono alle diverse colonne
	 * @return string
	 */
	private function printThead($cols, $header_tr=null, $header_th=array()) {
	
		$thead = '';
	
		if($header_tr or $header_th)
		{
			$header_tr = $header_tr ? " class=\"$header_tr\"" : '';
	
			$thead .= "<thead>";
			$thead .= "<tr".$header_tr.">";
	
			if(is_array($header_th) && count($header_th)) {
				$thead .= "<th class=\"$header_th[0]\"></th>";
			}
			else {
				$thead .= "<th></th>";
			}
	
			if($cols > 1 && is_array($header_th) && count($header_th)) {
					
				if(isset($header_th[1])) {
					$thead .= "<th class=\"$header_th[1]\"></th>";
				}
				else {
					$thead .= "<th></th>";
				}
			}
	
			if($cols > 2 && is_array($header_th) && count($header_th)) {
					
				if(isset($header_th[2])) {
					$thead .= "<th class=\"$header_th[2]\"></th>";
				}
				else {
					$thead .= "<th></th>";
				}
			}
	
			$thead .= "</tr>";
			$thead .= "</thead>";
		}
	
		return $thead;
	}
	
	/**
	 * @brief Gestisce il contenuto da mostrare
	 * @description Il contenuto viene mostrato in una cella di tabella con l'opzione @a add_td impostata a true.
	 * 
	 * @param mixed $item elemento da mostrare nella cella di tabella; può essere passto nei seguenti formati:
	 *   - object, oggetto dal quale recuperare i valori dei campi indicati nell'opzione @a field
	 *   - string, contenuto da mostrare nel tag TD
	 *   - array, tag TD personalizzato per uno specifico elemento; sono valide le seguenti opzioni
	 *     - @a custom_table (string), valore della classe del tag TABLE (se è attiva l'opzione @a as_table)
	 *     - @a custom_td (string), valore della classe del tag TD
	 *     - @a custom_data (mixed), oggetto o stringa come da parametro $item
	 * @param array $options array associativo di opzioni
	 *   opzioni generali
	 *   - @b add_td (boolean): indica se inserire il contenuto in una cella di tabella (all'interno del tag TD); default false
	 *   - @b add_table (boolean): indica se ritornare il contenuto come tabella completa (in questo caso inserire in cella di tabella, vedi l'opzione @a add_td true); default false
	 *   - @b css_table (string): nome della classe del tag TABLE (se è attiva l'opzione @a add_table)
	 *   - @b css_td (string): nome della classe del tag TD (se è attiva l'opzione @a add_td)
	 *   opzioni valide per un valore $item di tipo object
	 *   - @b view_checkbox (boolean): abilita la visualizzazione di un checkbox (default false)
	 *   - @b ckecked_ids (array): valori dei checkbox selezionati; il confronto viene effettuato col valore del campo @a id dell'oggetto $item
	 *   - @b field (mixed):
	 *     - string, nome del campo del modello da mostrare
	 *     - array, elenco dei nomi dei campi del modello da mostrare; i valori sono separati dal valore dell'opzione @a separator
	 *   - @b separator (string): separatore dei valori dei campi esplicitati nell'opzione @a field
	 * @return string
	 */
	protected function printCell($item, $options=array()) {
	
		$add_table = \Gino\gOpt('add_table', $options, false);
		$add_td = \Gino\gOpt('add_td', $options, false);
		$css_table = \Gino\gOpt('css_table', $options, null);
		$css_td = \Gino\gOpt('css_td', $options, null);
		$view_checkbox = \Gino\gOpt('view_checkbox', $options, false);
		$checked_ids = \Gino\gOpt('ckecked_ids', $options, array());
		$field = \Gino\gOpt('field', $options, null);
		$separator = \Gino\gOpt('separator', $options, null);
		
		// Cella personalizzata
		if(is_array($item))
		{
			$custom_table = \Gino\gOpt('custom_table', $item, null);
			$custom_td = \Gino\gOpt('custom_td', $item, null);
			$custom_data = \Gino\gOpt('custom_data', $item, null);
			
			if($custom_table) {
				$css_table = $custom_table;
			}
			if($custom_td) {
				$css_td = $custom_td;
			}
			$item = $custom_data;
		}
		// /End
	
		$css_table = $css_table ? " class=\"$css_table\"" : '';
		$css_td = $css_td ? " class=\"$css_td\"" : '';
	
		if(is_object($item))
		{
			if($view_checkbox) {
				$checked = in_array($item->id, $checked_ids) ? "checked=\"checked\"" : '';
				$print_checkbox = "<input type=\"checkbox\" $checked /> ";
			}
			else {
				$print_checkbox = '';
			}
			
			if(is_string($field))
			{
				$text = $item->$field;
			}
			elseif(is_array($field))
			{
				$text = '';
				$i = 1;
				$end = count($field);
	
				foreach($field AS $f)
				{
					$text .= $item->$f;
						
					if($i < $end) {
						$text .= $separator;
					}
					$i++;
				}
			}
			
			$buffer = $print_checkbox.$this->mText($text);
			if($add_td) {
				$buffer = "<td".$css_td.">".$buffer."</td>";
			}
		}
		elseif(is_string($item))
		{
			$buffer = $item;
			if($add_td) {
				$buffer = "<td".$css_td.">".$buffer."</td>";
			}
		}
		else
		{
			if($add_td) {
				$buffer = "<td".$css_td.">".$buffer."</td>";
			}
			else {
				$buffer = '';
			}
		}
		
		if($add_table) {
			$buffer = "<table".$css_table."><tr>$buffer</tr></table>";
		}
	
		return $buffer;
	}
}

/**
 * @brief Classe per la generazione di file pdf
 * 
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class plugin_mpdf {
	
    /**
     * @brief Directory temporanea di salvataggio dei file
     * @description mPDF is pre-configured to use <path to mpdf>/tmp/ as a directory to write temporary files (mainly for images). 
     * Write permissions must be set for read/write access for the tmp directory. 
     * As the default temp directory will be in vendor folder, is is advised to set custom temporary directory.
     * If you wish to use a different directory for temporary files, you should define tempDir key in constructor $config parameter.
     * 
     * @var string
     */
    private $_temp_dir;
    
	/**
	 * @brief Tipo di output
	 * @var string
	 */
	private $_output;
	
	/**
	 * @brief Modalità debug
	 * @var boolean
	 */
	private $_debug;
	
	/**
	 * Costruttore
	 * 
	 * @param array	options
	 *   array associativo di opzioni
	 *   - @b output (string): tipo di output del file pdf; deve essere conforme a quelli presenti nel metodo mpdf::outputs()
	 *     - @a inline: send to standard output; invia il file inline al browser (default)
	 *     - @a download: download file
	 *     - @a file: salva localmente il file; indicare il percorso assoluto
	 *     - @a string: ritorna una stringa
	 *   - @b debug (boolean): stampa a video il buffer (default false)
	 * @return void
	 */
	function __construct($options=array()){
		
	    $this->_temp_dir = CONTENT_DIR.OS.'tmp'.OS;
	    
	    if(array_key_exists('output', $options) && $options['output'])
		{
			$res = array_keys(self::outputs(), $options['output']);
			
			if($res && count($res)) {
				$this->_output = $res[0];
			}
			else {
				$this->_output = 'I';
			}
		}
		else $this->_output = 'I';
		
		$this->_debug = array_key_exists('debug', $options) && $options['debug'] ? $options['debug'] : false;
	}
	
	/**
	 * @brief Tipologie di output del pdf
	 * 
	 * @return array
	 */
	public static function outputs() {
		
		return array('F'=>'file', 'I'=>'inline', 'D'=>'download', 'S'=>'string');
	}
	
	/**
	 * @brief Imposta alcuni parametri di configurazione in uno script php
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b disable_error (boolean): blocca tutte le segnalazioni di errore (default false)
	 *   - @b memory_limit (string): quantità di memoria permessa a PHP nello script (es. 16M);
	 *     col valore '-1' => memoria infinita (pericoloso in produzione!)
	 *   - @b max_execution_time (integer): tempo massimo di esecuzione dello script in secondi (300 seconds = 5 minutes)
	 * @return null
	 */
	public static function setPhpParams($options=array()) {
		
		$disable_error = \Gino\gOpt('disable_error', $options, false);
		$memory_limit = \Gino\gOpt('memory_limit', $options, null);
		$max_execution_time = \Gino\gOpt('max_execution_time', $options, null);
		
		if(!is_null($memory_limit))
		{
			ini_set('memory_limit', $max_execution_time);
		}
		
		if(!is_null($max_execution_time))
		{
			ini_set('max_execution_time', $max_execution_time);
		}
		
		if($disable_error)
		{
			error_reporting(0);
		}
		
		return null;
	}
	
	/**
	 * Memoria allocata dallo script php
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b memory_usage (boolean): ritorna l'ammontare di memoria allocata da php (in byte);
	 *   si tratta della quantità di memoria utilizzata non appena viene eseguito lo script o delle singole istruzioni
	 *   - @b memory_peak_usage (boolean): ritorna il picco di memoria allocata da php (in byte)
	 * @return null
	 *
	 * @a memory_get_usage e @a memory_get_peak_usage prevedono l'opzione @a real_usage: \n
	 *   - true, ritorna la reale dimensione della memoria allocata dal sistema
	 *   - false (o non impostata), soltanto la memoria usata da emalloc()
	 * 
	 * Richiamando memory_get_peak_usage alla fine dello script si riuscirà a ricavare la più alta allocazione registrata durante l'esecuzione. \n
	 * Probabilmente è molto più utile questo valore che ottenere i valori di inizio e fine dello script
	 * in quanto in questo modo non si tiene conto della memoria allocata e poi deallocata durante il runtime.
	 */
	public static function getMemoryUsage($options=array()) {
		
		$memory_usage = \Gino\gOpt('memory_usage', $options, false);
		$memory_peak_usage = \Gino\gOpt('memory_peak_usage', $options, false);
		
		if($memory_usage)
		{
			echo \Gino\convertSize(memory_get_usage(true))."<br />";
		}
		
		if($memory_peak_usage)
		{
			echo \Gino\convertSize(memory_get_peak_usage(true))."<br />";
		}
		
		return null;
	}
	
	/**
	 * Estrapola il nome del file pdf
	 * 
	 * @param string $filename
	 * @return string
	 */
	private function conformFile($filename='') {
		
		if($filename)
		{
			$dirname = dirname($filename);
			if(!is_dir($dirname))
			{
				$filename = basename($filename);
			}
		}
		else $filename = '';
		
		return $filename;
	}
	
	/**
	 * Imposta header e footer
	 * 
	 * @param array options
	 *   - @b css_file (mixed):
	 *     - string, percorso al file css (default css/mpdf.css)
	 *     - array, elenco dei file css da caricare
	 *   - @b css_style (string): stili css personalizzati (in un tag style)
	 *   - @b header (string): header personalizzato
	 *   - @b footer (mixed):
	 *     - boolean, col valore @a false il footer non viene mostrato
	 *     - string, footer personalizzato, sono implementate le stringhe sostitutive:
	 *       - @a _NUMPAGE_, numero di pagina
	 *       - @a _TOTPAGE_, numero totale di pagine
	 *     - in tutti gli altri casi viene mostrato il footer standard
	 *  @return string
	 */
	public function htmlStart($options=array()){
		
		$css_file = array_key_exists('css_file', $options) ? $options['css_file'] : "css/mpdf.css";
		$css_style = array_key_exists('css_style', $options) ? $options['css_style'] : '';
		$header = array_key_exists('header', $options) ? $options['header'] : '';
		$footer = array_key_exists('footer', $options) ? $options['footer'] : '';
		
		$html = "<html>";
		$html .= "<head>";
		
		if(is_array($css_file) && count($css_file))
		{
			foreach($css_file AS $item)
			{
				$html .= "<link href=\"$item\" type=\"text/css\" rel=\"stylesheet\" />";
			}
		}
		else
		{
			$html .= "<link href=\"$css_file\" type=\"text/css\" rel=\"stylesheet\" />";
		}
		
		if($css_style)
			$html .= "<style>".$css_style."</style>";
		
		$html .= "</head>";
		$html .= "<body>\n";
		
		if(is_bool($footer) && $footer===false)
		{
			$footer = '';
		}
		elseif(is_string($footer) && $footer)
		{
			if(preg_match('#_NUMPAGE_#', $footer))
				$footer = preg_replace('#_NUMPAGE_#', '{PAGENO}', $footer);
			if(preg_match('#_TOTPAGE_#', $footer))
				$footer = preg_replace('#_TOTPAGE_#', '{nb}', $footer);
		}
		else
		{
			$footer = $this->defaultFooter();
		}
		$html .= "
<!--mpdf
<htmlpageheader name=\"myheader\">
$header
</htmlpageheader>

<htmlpagefooter name=\"myfooter\">
$footer
</htmlpagefooter>

<sethtmlpageheader name=\"myheader\" value=\"on\" show-this-page=\"1\" />
<sethtmlpagefooter name=\"myfooter\" value=\"on\" />
mpdf-->";
			
		return $html;
	}
	
	/**
	 * Footer standard
	 * 
	 * @return string
	 */
	public function defaultFooter() {
		
		$footer = "
<div style=\"border-top: 1px solid #000000; font-size: 6pt; text-align: center; padding-top: 3mm; \">
"._("Pagina")." {PAGENO} "._("di")." {nb}
</div>";
		return $footer;
	}
	
	/**
	 * Chiusura del testo html
	 * 
	 * @return string
	 */
	public function htmlEnd(){
		
		$html = "</body>\n";
		$html .= "</html>\n";
		return $html;
	}
	
	/**
	 * Processa il testo HTML per renderlo compatibile con la generazione del pdf
	 * 
	 * @see func.mpdf.php, pdfHtmlToEntities()
	 * @param string $html testo html
	 * @return string or print (debug)
	 */
	public function htmlCreate($html){
		
		$html = \Gino\pdfHtmlToEntities($html);
		$html = utf8_encode($html);
		
		if($this->_debug) {
			echo $html;
		}
		else {
			return $html;
		}
	}
	
	/**
	 * Definizione del contenuto html
	 * 
	 * @see htmlStart()
	 * @see htmlEnd()
	 * @see htmlCreate()
	 * @param string $text
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b css_file (mixed): percorso ai file css inclusi nel pdf
	 *     - @a array, elenco dei file (ad esempio array('app/news/css/pdf.css', 'app/news/css/local.css'))
	 *     - @a string
	 *   - @b header (string)
	 *   - @b footer (string)
	 *   - @b debug_exit (boolean): interrompe il flusso dell'html nel caso di debug attivo
	 * @return string
	 */
	public function definePage($text, $options=array()) {
		
		$css_file = \Gino\gOpt('css_file', $options, null);
		$header = \Gino\gOpt('header', $options, null);
		$footer = \Gino\gOpt('footer', $options, null);
		$debug_exit = \Gino\gOpt('debug_exit', $options, true);
		
		$buffer = $this->htmlStart(array('header'=>$header, 'footer'=>$footer, 'css_file'=>$css_file));
		$buffer .= $text;
		$buffer .= $this->htmlEnd();
		$buffer = $this->htmlCreate($buffer);
		
		if($this->_debug && $debug_exit) {
			exit();
		}
		
		return $buffer;
	}
	
	/**
	 * @brief Crea il file pdf
	 * 
	 * @see \Mpdf\Mpdf::WriteHTML()
	 * @see \Mpdf\Mpdf::Output()
	 * @param string $filename nome del file pdf
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b title (string): titolo del pdf
	 *   - @b author (string): autore del pdf
	 *   - @b creator (string): chi ha generato il pdf
	 *   - @b watermark (boolean): scritta in sovraimpressione (default false)
	 *   - @b watermark_text (string): testo della scritta in sovraimpressione (default 'esempio')
	 *   - @b format (string|array): formato della pagina
	 *     - @a string, A4 (default), A4-L, A3, Letter, ...
	 *     - @a array, [width, height] in millimetres, for example [190, 236]
	 *   - @b landscape (boolean): orientamento orizzontale della pagina (default false)
	 *   - @b mode (string): codifica del testo (default utf-8)
	 *   - @b protection (array): crittografa e imposta i permessi per il file pdf; il valore di default è null, ovvero il documento non è crittografato e garantisce tutte le autorizzazioni all'utente. \n
	 *     L'array può includere alcuni, tutti o nessuno dei seguenti valori che indicano i permessi concessi:
	 *     - @a copy
	 *     - @a print
	 *     - @a modify
	 *     - @a annot-forms
	 *     - @a fill-forms
	 *     - @a extract
	 *     - @a assemble
	 *     - @a print-highres
	 *   - @b user_password (string): password utente del pdf
	 *   - @b owner_password (string): password del proprietario del pdf
	 *   - @b font_size (integer)
	 *   - @b font (string)
	 *   - @b margin_left (integer): distance in mm from left of page
	 *   - @b margin_right (integer): distance in mm from right of page
	 *   - @b margin_top (integer): distance in mm from top of page to start of text (ignoring any headers)
	 *   - @b margin_bottom (integer): distance in mm from bottom of page to bottom of text (ignoring any footers)
	 *   - @b margin_header (integer): distance in mm from top of page to start of header
	 *   - @b margin_footer (integer): distance in mm from bottom of page to bottom of footer
	 *   - @b orientation (string): specifica l'orientamento di una nuova pagina; 
	 *     if format parameter is defined as a string, the orientation parameter will be ignored; valid values:
	 *     - @a L, landscape
	 *     - @a P, portrait (default)
	 *   - @b simpleTables (boolean): disabilita gli stili css complessi delle tabelle (bordi, padding, ecc.) per incrementare le performance (default false)
	 *   - @b showStats (boolean): visualizza i valori di performance relativi al file pdf (default false); 
	 *     l'opzione sopprime l'output del file pdf e visualizza i dati sul browser, tipo:
	 *     @code
	 *     Generated in 0.45 seconds
	 *     Compiled in 0.46 seconds (total)
	 *     Peak Memory usage 10.25MB
	 *     PDF file size 37kB
	 *     Number of fonts 6
	 *     @endcode
	 *   - @b progressBar (mixed): abilita la visualizzazione di una barra di progresso durante la generazione del file; 
	 *     non è raccomandata come utilizzo generale ma può essere utile in ambiente di sviluppo e nella generazione lenta di documenti
	 *     - 1, visualizza la progress bar
	 *     - 2, visualizza più di una progress bar per un esame dettagliato del progresso
	 *     - false, disabilita la progress bar (default)
	 *   - @b progbar_heading (string): heading personalizzato della progressBar
	 *   - @b progbar_altHTML (string): progressBar personalizzata (html)
	 *   opzioni sui contenuti
	 *   - @b content (mixed): contenuto del file; se nullo legge il metodo self::content()
	 *     - @a string, contenuti con pagine aventi la stessa formattazione
	 *     - @a array, contenuti con pagine che possono cambiare formattazione, come ad esempio l'orientamento; struttura dell'array:
	 *     array([, string html], array(orientation=>[, string [L|P]], html=>[, string]), ...)
	 *   - @b object (object): oggetto @a gino_mpdf
	 *   opzioni dei metodi Gino.Plugin.gino_mpdf::header e Gino.Plugin.gino_mpdf::footer
	 *   - @b img_dir (string): percorso ai file immagine di header/footer
	 * @return mixed
	 *   - string (output string)
	 *   - exit (output inline e download)
	 *   - boolean true (output file)
	 * 
	 * Esempio:
	 * @code
	 * $pdf->makeFile(
	 *   $filename, 
	 *   array(
	 *     'title'=>_("Progetto"), 
	 *     'author'=>_("Otto Srl"), 
	 *     'creator'=>_("Marco Guidotti"), 
	 *     'content'=>array($html_page1, array('orientation'=>'L', 'html'=>$html_page2)), 
	 *     'object'=>$this
	 * ));
	 * @endcode
	 * 
	 * Default values:
	 *   margin_left: 15
	 *   margin_right: 15
	 *   margin_top: 16
	 *   margin_bottom: 16
	 *   margin_header: 9
	 *   margin_footer: 9
	 */
	public function makeFile($filename, $options=array()){
		
		$title = \Gino\gOpt('title', $options, '');
		$author = \Gino\gOpt('author', $options, '');
		$creator = \Gino\gOpt('creator', $options, '');
		$watermark = \Gino\gOpt('watermark', $options, false);
		$watermark_text = \Gino\gOpt('watermark_text', $options, _("esempio"));
		
		$format = array_key_exists('format', $options) && $options['format'] ? $options['format'] : 'A4';
		$landscape = \Gino\gOpt('landscape', $options, false);
		$mode = array_key_exists('mode', $options) && $options['mode'] ? $options['mode'] : 'utf-8';
		
		$protection = \Gino\gOpt('protection', $options, null);
		$user_password = \Gino\gOpt('user_password', $options, '');
		$owner_password = \Gino\gOpt('owner_password', $options, '');
		
		$default_font_size = \Gino\gOpt('font_size', $options, 0);
		$default_font = \Gino\gOpt('font', $options, '');
		$orientation = \Gino\gOpt('orientation', $options, 'P');
		$simple_tables = \Gino\gOpt('simpleTables', $options, false);
		$show_stats = \Gino\gOpt('showStats', $options, false);
		$progress_bar = \Gino\gOpt('progressBar', $options, false);
		$progress_bar_heading = \Gino\gOpt('progbar_heading', $options, null);
		$progress_bar_alt = \Gino\gOpt('progbar_altHTML', $options, null);
		
		if($landscape) $format .= '-L';
		
		if($format == 'A4' || $format == 'A3')
		{
		    $margin_left = 20;
		    $margin_right = 15;
		    $margin_top = 25;
		    $margin_bottom = 25;
		    $margin_header = 10;
		    $margin_footer = 10;
		}
		else	// Valori di default come nel costruttore della classe \Mpdf\Mpdf
		{
		    $margin_left = 15;
		    $margin_right = 15;
		    $margin_top = 16;
		    $margin_bottom = 16;
		    $margin_header = 9;
		    $margin_footer = 9;
		}
		
		// Personalizzazione dei parametri
		if(array_key_exists('margin_left', $options) && !is_null($options['margin_left'])) $margin_left = $options['margin_left'];
		if(array_key_exists('margin_right', $options) && !is_null($options['margin_right'])) $margin_right = $options['margin_right'];
		if(array_key_exists('margin_top', $options) && !is_null($options['margin_top'])) $margin_top = $options['margin_top'];
		if(array_key_exists('margin_bottom', $options) && !is_null($options['margin_bottom'])) $margin_bottom = $options['margin_bottom'];
		if(array_key_exists('margin_header', $options) && !is_null($options['margin_header'])) $margin_header = $options['margin_header'];
		if(array_key_exists('margin_footer', $options) && !is_null($options['margin_footer'])) $margin_footer = $options['margin_footer'];
		
		$mpdf = new \Mpdf\Mpdf([
			'mode' => $mode, 
		    'format' => $format, 
			'default_font_size' => $default_font_size, 
		    'default_font' => $default_font, 
		    'margin_left' => $margin_left, 
		    'margin_right' => $margin_right, 
		    'margin_top' => $margin_top, 
		    'margin_bottom' => $margin_bottom, 
		    'margin_header' => $margin_header, 
		    'margin_footer' => $margin_footer, 
		    'orientation' => $orientation,
		    'tempDir' => $this->_temp_dir,
		]);
		
		$mpdf->simpleTables = $simple_tables;
		$mpdf->showStats = $show_stats;
		$mpdf->useOnlyCoreFonts = true;
		if(is_array($protection)) {
			$mpdf->SetProtection($protection, $user_password, $owner_password);
		}
		$mpdf->SetTitle($title);
		$mpdf->SetAuthor($author);
		$mpdf->SetCreator($creator);
		$mpdf->SetWatermarkText($watermark_text);
		$mpdf->showWatermarkText = $watermark;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');
		
		//$mpdf->allow_charset_conversion = true;
		//$mpdf->charset_in = 'iso-8859-1';	// default 'utf-8'
		//$mpdf->shrink_tables_to_fit = 0;	// prevent all tables from resizing
		
		// Progress bar
		if($progress_bar)
		{
			if($progress_bar_heading) $mpdf->progbar_heading = $progress_bar_heading;
			if($progress_bar_alt) $mpdf->progbar_altHTML = $progress_bar_alt;
			if($progress_bar === true) $progress_bar = 1;
			
			$mpdf->StartProgressBarOutput($progress_bar);
		}
		
		// Def contents
		$content = \Gino\gOpt('content', $options, null);
		$object = \Gino\gOpt('object', $options, null);
		
		if(!$content && is_object($object)) {
			$content = $object->content($options);
		}
		
		if(is_array($content))
		{
			$tmp = $content;
		}
		else
		{
			if(is_object($object))	// obj gino_mpdf
			{
				$options['header'] = $object->header($options);
				$options['footer'] = $object->footer($options);
			}
			
			$tmp = $this->definePage($content, $options);
		}
		// /Def
		
		if(is_string($tmp))
		{
			$mpdf->WriteHTML($tmp);
		}
		elseif(is_array($tmp) AND sizeof($tmp) > 0)
		{
			$pages = $tmp;
			for($i=0, $end=sizeof($pages); $i<$end; $i++)
			{
				if(is_array($pages[$i]))
				{
				    // Optional parameters @see \Mpdf\Mpdf::AddPageByArray()
					$orientation_page = array_key_exists('orientation', $pages[$i]) ? $pages[$i]['orientation'] : 'P';
					$resetpagenum = array_key_exists('resetpagenum', $pages[$i]) ? $pages[$i]['resetpagenum'] : null;
					$suppress = array_key_exists('suppress', $pages[$i]) ? $pages[$i]['suppress'] : null;
					$margin_left = array_key_exists('margin_left', $pages[$i]) ? $pages[$i]['margin_left'] : null;
					$margin_right = array_key_exists('margin_right', $pages[$i]) ? $pages[$i]['margin_right'] : null;
					$margin_top = array_key_exists('margin_top', $pages[$i]) ? $pages[$i]['margin_top'] : null;
					$margin_bottom = array_key_exists('margin_bottom', $pages[$i]) ? $pages[$i]['margin_bottom'] : null;
					$margin_header = array_key_exists('margin_header', $pages[$i]) ? $pages[$i]['margin_header'] : null;
					$margin_footer = array_key_exists('margin_footer', $pages[$i]) ? $pages[$i]['margin_footer'] : null;
					// /parameters
					
					// Custom header/footer
					$header_page = array_key_exists('header_page', $pages[$i]) ? $pages[$i]['header_page'] : null;
					$footer_page = array_key_exists('footer_page', $pages[$i]) ? $pages[$i]['footer_page'] : null;
					
					$html = array_key_exists('html', $pages[$i]) ? $pages[$i]['html'] : '';
					$debug_exit_page = array_key_exists('debug_exit', $pages[$i]) ? $pages[$i]['debug_exit'] : true;
				}
				else
				{
					$orientation_page = $landscape ? 'L' : 'P';
					$resetpagenum = null;
					$suppress = null;
					$margin_left = null;
					$margin_right = null;
					$margin_top = null;
					$margin_bottom = null;
					$margin_header = null;
					$margin_footer = null;
					
					$header_page = null;
					$footer_page = null;
					
					$html = $pages[$i];
					$debug_exit_page = true;
				}
				
				$opt = $options;
				if($header_page) {
					$opt['header'] = $header_page;
				}
				if($footer_page) {
					$opt['footer'] = $footer_page;
				}
				$opt['debug_exit'] = $debug_exit_page;
				
				$html = $this->definePage($html, $opt);
				
				$option_page = array('orientation'=>$orientation_page);
				
				if(!is_null($resetpagenum)) $option_page['resetpagenum'] = $resetpagenum;
				if(!is_null($suppress)) $option_page['suppress'] = $suppress;
				if(!is_null($margin_left)) $option_page['margin_left'] = $margin_left;
				if(!is_null($margin_right)) $option_page['margin_right'] = $margin_right;
				if(!is_null($margin_top)) $option_page['margin_top'] = $margin_top;
				if(!is_null($margin_bottom)) $option_page['margin_bottom'] = $margin_bottom;
				if(!is_null($margin_header)) $option_page['margin_header'] = $margin_header;
				if(!is_null($margin_footer)) $option_page['margin_footer'] =  $margin_footer;
				
				$mpdf->AddPageByArray($option_page);
				$mpdf->WriteHTML($html);
			}
		}
		
		$filename = $this->conformFile($filename);
		
		if($this->_output == 'S')
		{
			return $mpdf->Output($filename, $this->_output);
		}
		elseif($this->_output == 'I' || $this->_output == 'D')
		{
			$mpdf->Output($filename, $this->_output);
			exit();
		}
		else	// F
		{
			$mpdf->Output($filename, $this->_output);
			return true;
		}
	}
	
	/**
	 * @brief Invia il file pdf come allegato email
	 * 
	 * @param string $mpdf_output output con opzione 'string'
	 * @param string $filename nome del file allegato alla email
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b mailto (string)
	 *   - @b from_name (string)
	 *   - @b from_mail (string)
	 *   - @b replyto (string)
	 *   - @b subject (string)
	 *   - @b message (string)
	 * @return void
	 * 
	 * @todo Verificare se occorre utilizzare \n al posto di \r\n
	 */
	public function sendToEmail($mpdf_output, $filename, $options=array()){
		
		$mailto = array_key_exists('mailto', $options) ? $options['mailto'] : '';
		$from_name = array_key_exists('from_name', $options) ? $options['from_name'] : '';
		$from_mail = array_key_exists('from_mail', $options) ? $options['from_mail'] : '';
		$replyto = array_key_exists('replyto', $options) ? $options['replyto'] : '';
		$subject = array_key_exists('subject', $options) ? $options['subject'] : '';
		$message = array_key_exists('message', $options) ? $options['message'] : '';
		
		$content = chunk_split(base64_encode($mpdf_output));
		
		$filename = $this->conformFile($filename);
		
		$uid = md5(uniqid(time()));
		
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
		$header .= "Content-Transfer-Encoding: base64\r\n";
		$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$header .= $content."\r\n\r\n";
		$header .= "--".$uid."--";
		$is_sent = @mail($mailto, $subject, "", $header);

		exit();
	}
	
	/**
	 * Formatta il contenuto da salvare in un campo del database
	 * 
	 * @param string $mpdf_output output con opzione 'string'
	 * @return string
	 */
	public function dataToDB($mpdf_output) {
		
		$string = bin2hex($mpdf_output);
		$string = "0x".$string;
		
		return $string;
	}
	
	/**
	 * Recupera il file pdf salvato come stringa in un record del database
	 * 
	 * @param string $data
	 */
	public function getToDataDB($data) {
		
		$pdf = pack("H*", $data );
		header('Content-Type: application/pdf');
		header('Content-Length: '.strlen($pdf));
		header('Content-disposition: inline; filename="'.$name.'"');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		echo $pdf;
		exit;
	}
	
	/**
	 * Break di pagina
	 * 
	 * @return string
	 */
	public function breakpage(){
		
		return "<pagebreak />";
	}
	
	/**
	 * Contenitore di testo
	 * 
	 * @param string $text
	 * @return string
	 */
	public function longText($text){
		
		if(!empty($text)) {
			$text = "<div class=\"longtext\">$text</div>";
		}
		return $text;
	}
	
	/**
	 * Gestione del testo
	 * 
	 * @param string $text
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b class (string): classe del tag span, es. 'label'
	 *   - @b style (string): stile del tag span, es. 'color:#000000; font-size:10px';
	 *   - @b other (string): altro nel tag span
	 *   - @b type (string): tipo di dato (default @a text)
	 *     - @a text, richiama la funzione Gino.pdfChars()
	 *     - @a textarea, richiama la funzione Gino.pdfChars_Textarea()
	 *     - @a editor, richiama la funzione Gino.pdfTextChars()
	 * @return string
	 */
	public function text($text, $options=array()){
		
		$class = \Gino\gOpt('class', $options, '');
		$style = \Gino\gOpt('style', $options, '');
		$other= \Gino\gOpt('other', $options, '');
		$type = \Gino\gOpt('type', $options, 'text');
		
		if($class) {
		    $class = "class=\"$class\"";
		}
		if($style) {
		    $style = "style=\"$style\"";
		}
		
		if($type == 'textarea') {
		    $method = '\Gino\pdfChars_Textarea';
		}
		elseif($type == 'editor') {
		    $method = '\Gino\pdfTextChars';
		}
		else {
		    $method = '\Gino\pdfChars';
		}
		
		$text = $method($text);
		
		if($class OR $style OR $other) {
			$text = "<span $class$style$other>$text</span>";
		}
		
		return $text;
	}
}
?>