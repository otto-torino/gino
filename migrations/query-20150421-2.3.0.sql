-- 
-- Set of query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 
-- Data query: changes of labels or translations (no essential)
-- Structure query: changes of structure

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------

-- Data query
INSERT INTO `page_entry` (`category_id`, `author`, `creation_date`, `last_edit_date`, `title`, `slug`, `image`, `url_image`, `text`, `tags`, `enable_comments`, `published`, `social`, `private`, `users`, `read`, `tpl_code`, `box_tpl_code`) VALUES
(NULL, 1, '2015-05-11 15:05:21', '2015-06-03 17:06:45', 'Privacy - Cookie', 'privacy-cookie', NULL, NULL, '<p>Con riferimento all''art. 122 secondo comma del D.lgs. 196/2003 e a seguito delle modalità semplificate per l''informativa e l''acquisizione del consenso per l''uso dei cookie pubblicata sulla Gazzetta Ufficiale n.126 del 3 giugno 2014 e relativo registro dei provvedimenti n.229 dell''8 maggio 2014, si dichiara che:</p>\r\n\r\n<p>1 - Il sito web <b>NOMESITO</b> (più avanti "Sito") utilizza i cookie per offrire i propri servizi agli Utenti durante la consultazione delle sue pagine. Titolare del trattamento dei dati è <b>NOMEAZIENDA</b> (informazioni di contatto in fondo ad ogni pagina).</p>\r\n\r\n<p>2 - L''informativa è valida solo per il Sito e per gli eventuali domini di secondo e terzo livello correlati, e non per altri siti consultabili tramite link.</p>\r\n\r\n<p>3 - Se l''utente non acconsente all''utilizzo dei cookie, non accettando in maniera esplicita i cookie di navigazione, o mediante specifiche configurazioni del browser utilizzato o dei relativi programmi informatici utilizzati per navigare le pagine che compongono il Sito, o modificando le impostazioni nell''uso dei servizi di terze parti utilizzati all''interno del Sito, l''esperienza di navigazione potrebbe essere penalizzata, ed alcune funzionalità potrebbero non essere disponibili.</p>\r\n\r\n<p>4 - Il Sito NON fa uso diretto (first-part cookie) di cookie di PROFILAZIONE degli utenti.</p>\r\n\r\n<p>5 - Il Sito NON consente l''invio di cookie di PROFILAZIONE di TERZE PARTI (third-part cookie).</p>\r\n\r\n<p>6 - Il Sito fa uso diretto esclusivamente di cookie TECNICI per salvare i parametri di sessione e agevolare quindi la navigazione agli utenti.</p>\r\n\r\n<p>7 - Il Sito potrà fare uso di cookie TECNICI di terze parti (non tutti i servizi sono per forza attivi):<br />\r\n<br />\r\n<b>Google</b><br />\r\nIl servizio Google Analytics viene utilizzato per raccogliere statistiche ANONIME di accesso, monitorare e analizzare i dati di traffico.<br />\r\nI servizi Google Maps e YouTube sono utilizzati per includere contenuti multimediali all''interno del sito.<br />\r\n<a href="http://www.google.com/policies/technologies/types/" rel="external">Informazioni generali</a> | <a href="http://www.google.com/policies/privacy/" rel="external">Privacy Policy</a> | <a href="http://tools.google.com/dlpage/gaoptout?hl=it" rel="external">Opt Out</a></p>\r\n\r\n<p><b>ShareThis</b><br />\r\nIl servizio ShareThis viene utilizzato per facilitare la condivisione dei contenuti sulle più comuni piattaforme social.<br />\r\n<a href="http://www.sharethis.com/legal/privacy/" rel="external">Privacy Policy</a> | <a href="http://www.sharethis.com/legal/privacy/" rel="external">Opt Out</a></p>\r\n\r\n<p><b>Disqus</b><br />\r\nIl servizio viene utilizzato per facilitare e migliorare la gestione dei commenti ai contenuti.<br />\r\n<a href="https://help.disqus.com/customer/portal/articles/466259-privacy-policy" rel="external">Privacy Policy</a> | <a href="https://help.disqus.com/customer/portal/articles/1657951" rel="external">Opt Out</a></p>\r\n\r\n<p><b>Vimeo</b><br />\r\nIl popolare servizio di streaming video utilizza i cookie per ottimizzare la fruizione dei suoi servizi, e il alcuni casi il Sito può includere video Vimeo.<br />\r\n<a href="https://vimeo.com/cookie_policy" rel="external">Cookie Policy</a></p>\r\n\r\n<p><b>Bottoni Social</b><br />\r\nI bottoni social sono bottoni che permettono di rendere più immediata ed agevole la condivisione dei contenuti sulle più comuni piattaforme social. Qui di seguito i dettagli dei principali servizi:</p>\r\n\r\n<p><b>Pulsante +1 e widget sociali di Google+</b> (Google Inc.)<br />\r\nIl pulsante +1 e i widget sociali di Google+ (tra cui i commenti) sono servizi di interazione con il social network Google+, forniti da Google Inc.<br />\r\nDati personali raccolti: Cookie e Dati di utilizzo.<br />\r\nLuogo del trattamento : USA - <a href="http://www.google.com/intl/it/policies/privacy/" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Pulsante "Mi Piace" e widget sociali di Facebook</b> (Facebook, Inc.)<br />\r\nIl pulsante "Mi Piace" e i widget sociali di Facebook sono servizi di interazione con il social network Facebook, forniti da Facebook, Inc.<br />\r\nDati personali raccolti: Cookie e Dati di utilizzo.<br />\r\nLuogo del trattamento : USA - <a href="http://www.facebook.com/privacy/explanation.php" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Pulsante Tweet e widget sociali di Twitter</b> (Twitter, Inc.)<br />\r\nIl pulsante Tweet e i widget sociali di Twitter sono servizi di interazione con il social network Twitter, forniti da Twitter, Inc.<br />\r\nDati personali raccolti: Cookie e Dati di utilizzo.<br />\r\nLuogo del trattamento : USA - <a href="http://twitter.com/privacy" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Pulsante e widget sociali di Linkedin</b> (Linkedin Corp.)<br />\r\nIl pulsante e i widget sociali di Linkedin sono servizi di interazione con il social network Linkedin, forniti da Linkedin Inc.<br />\r\nDati personali raccolti: Cookie e Dati di navigazione ed utilizzo.<br />\r\nLuogo del Trattamento: USA - <a href="http://www.linkedin.com/static?key=privacy_policy&trk=hb_ft_priv" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Cookie Script</b><br />\r\nIl Sito utilizza il servizio Cookie Script per l''accettazione dell''utilizzo dei cookies. Se acconsenti all''utilizzo dei cookies, un ulteriore cookie tecnico di nome cookiescriptaccept verrà scritto per ricordare in futuro la tua scelta.<br />\r\n<a href="https://cookie-script.com/privacy-policy-and-disclaimer.html" rel="external">Privacy Policy</a></p>\r\n\r\n<p>8 - Questa pagina è raggiungibile mediante un link presente in tutte le pagine del Sito.</p>\r\n\r\n<p>9 - Negando il consenso all''utilizzo dei cookie, nessun cookie verrà scritto sul dispositivo dell''utente, eccetto il cookie tecnico di sessione. Sarà ancora possibile navigare il Sito, ma alcune parti di esso potrebbero non funzionare correttamente.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>Ma... cosa sono i cookie?</b></p>\r\n\r\n<p>I cookie sono file o pacchetti di dati che possono venire salvati sul computer dell''utente (o altro dispositivo abilitato alla navigazione su internet, per esempio smartphone o tablet) quando visita un sito web. Di solito un cookie contiene il nome del sito internet dal quale il cookie stesso proviene, la durata del cookie (ovvero l''indicazione del tempo per il quale il cookie rimarrà memorizzato sul dispositivo), ed un contenuto (numero, stringa, etc.), che gli permette di svolgere la sua funzione.<br />\r\nPer maggiori informazioni visita il sito in lingua inglese <a href="http://aboutcookies.org/." rel="external">aboutcookies.org</a>.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>Per cosa si usano i cookie?</b></p>\r\n\r\n<p>Si utilizzano i cookie per rendere la navigazione più semplice e per meglio adattare il sito web ai bisogni dell''utente. I cookie possono anche venire usati per aiutare a velocizzare le future esperienze ed attività dell''utente su altri siti web, e si usano per compilare statistiche anonime aggregate che consentono di capire come gli utenti usano i siti in modo da aiutare a migliorare la struttura ed i contenuti di questi siti.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>I diversi tipi di cookie</b></p>\r\n\r\n<p><b>Cookies Tecnici</b>: sono i cookie che servono a effettuare la navigazione o a fornire un servizio richiesto dall''utente. Non vengono utilizzati per scopi ulteriori e sono normalmente installati direttamente dal gestore del sito web che si sta novigando. Senza il ricorso a tali cookie, alcune operazioni non potrebbero essere compiute o sarebbero più complesse e/o meno sicure, (ad esempio i cookie che consentono di effettuare e mantenere l''identificazione dell''utente nell''ambito della sessione).</p>\r\n\r\n<p><b>Cookies di Profilazione</b>: sono i cookie utilizzati per tracciare la navigazione dell''utente in rete e creare profili sui suoi gusti, abitudini, scelte, ecc. Con questi cookie possono essere trasmessi al terminale dell''utente messaggi pubblicitari in linea con le preferenze già manifestate dallo stesso utente nella navigazione online.</p>\r\n\r\n<p><b>Cookies di prima parte</b> (first-part cookie) sono i cookie generati e utilizzati direttamente dal soggetto gestore del sito web sul quale l''utente sta navigando.</p>\r\n\r\n<p><b>Cookies di terza parte</b> (third-part cookie), sono i cookie generati e gestiti da soggetti diversi dal gestore del sito web sul quale l''utente sta navigando (in forza, di regola, di un contratto tra il titolare del sito web e la terza parte)</p>\r\n\r\n<p><b>Cookies di Sessione</b> e <b>Cookies Persistenti</b>:<br />\r\nmentre la differenza tra un cookie di prima parte e un cookie di terzi riguarda il soggetto che controlla l''invio iniziale del cookie sul tuo dispositivo, la differenza tra un cookie di sessione e un cookie persistente riguarda il diverso lasso di tempo per cui un cookie opera. I cookie di sessione sono cookie che tipicamente durano finchè chiudi il tuo internet browser. Quando finisci la tua sessione browser, il cookie scade. I cookies persistenti, come lo stesso nome indica, sono cookie costanti e continuano ad operare dopo che hai chiuso il tuo browser.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>Come posso controllare le gestione dei cookie del mio browser?</b></p>\r\n\r\n<p>Tutti i moderni browser offrono la possibilita di controllare le impostazioni di privacy, anche per quello che riguarda l''uso dei cookie. In particolare l''utente potrà intervenire sul comportamento generale del browser nei confronti dei cookie (ad esempio instruendolo a NON accettarli in futuro), visualizzare e/o cancellare i cookie già installati.<br />\r\n<br />\r\nRiportiamo qui di seguito le procedure per accedere a queste impostazioni per i browser più utilizzati:<br />\r\n<br />\r\n<a href="https://support.google.com/chrome/answer/95647?hl=it" rel="external">Chrome</a></p>\r\n\r\n<p><a href="http://windows.microsoft.com/it-it/windows-vista/block-or-allow-cookies" rel="external">Internet Explorer</a></p>\r\n\r\n<p><a href="https://support.mozilla.org/it/kb/Gestione%20dei%20cookie" rel="external">Firefox</a></p>\r\n\r\n<p><a href="http://www.opera.com/help/tutorials/security/privacy/" rel="external">Opera</a></p>\r\n\r\n<p><a href="https://support.apple.com/kb/PH17191?locale=en_US" rel="external">Safari 6/7</a></p>\r\n\r\n<p><a href="https://support.apple.com/kb/PH19214?locale=en_US" rel="external">Safari 8</a></p>\r\n\r\n<p><a href="https://support.apple.com/en-us/HT201265" rel="external">Safari mobile</a></p>', NULL, 0, 1, 0, 0, NULL, 0, NULL, NULL);

-- Structure query
ALTER TABLE `sys_conf` ADD `query_cache` TINYINT(1) NOT NULL DEFAULT '0', ADD `query_cache_time` SMALLINT(4) NULL;

ALTER TABLE `page_opt` CHANGE `newsletter_tpl_code` `newsletter_tpl_code` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
INSERT INTO page_entry (category_id, author, creation_date, last_edit_date, title, slug, image, url_image, text, tags, enable_comments, published, social, private, users, [read], tpl_code, box_tpl_code) VALUES
(NULL, 1, '2015-05-11 15:05:21', '2015-06-03 17:06:45', 'Privacy - Cookie', 'privacy-cookie', NULL, NULL, '<p>Con riferimento all''art. 122 secondo comma del D.lgs. 196/2003 e a seguito delle modalità semplificate per l''informativa e l''acquisizione del consenso per l''uso dei cookie pubblicata sulla Gazzetta Ufficiale n.126 del 3 giugno 2014 e relativo registro dei provvedimenti n.229 dell''8 maggio 2014, si dichiara che:</p>

<p>1 - Il sito web <b>NOMESITO</b> (più avanti "Sito") utilizza i cookie per offrire i propri servizi agli Utenti durante la consultazione delle sue pagine. Titolare del trattamento dei dati è <b>NOMEAZIENDA</b> (informazioni di contatto in fondo ad ogni pagina).</p>

<p>2 - L''informativa è valida solo per il Sito e per gli eventuali domini di secondo e terzo livello correlati, e non per altri siti consultabili tramite link.</p>

<p>3 - Se l''utente non acconsente all''utilizzo dei cookie, non accettando in maniera esplicita i cookie di navigazione, o mediante specifiche configurazioni del browser utilizzato o dei relativi programmi informatici utilizzati per navigare le pagine che compongono il Sito, o modificando le impostazioni nell''uso dei servizi di terze parti utilizzati all''interno del Sito, l''esperienza di navigazione potrebbe essere penalizzata, ed alcune funzionalità potrebbero non essere disponibili.</p>

<p>4 - Il Sito NON fa uso diretto (first-part cookie) di cookie di PROFILAZIONE degli utenti.</p>

<p>5 - Il Sito NON consente l''invio di cookie di PROFILAZIONE di TERZE PARTI (third-part cookie).</p>

<p>6 - Il Sito fa uso diretto esclusivamente di cookie TECNICI per salvare i parametri di sessione e agevolare quindi la navigazione agli utenti.</p>

<p>7 - Il Sito potrà fare uso di cookie TECNICI di terze parti (non tutti i servizi sono per forza attivi):<br />
<br />
<b>Google</b><br />
Il servizio Google Analytics viene utilizzato per raccogliere statistiche ANONIME di accesso, monitorare e analizzare i dati di traffico.<br />
I servizi Google Maps e YouTube sono utilizzati per includere contenuti multimediali all''interno del sito.<br />
<a href="http://www.google.com/policies/technologies/types/" rel="external">Informazioni generali</a> | <a href="http://www.google.com/policies/privacy/" rel="external">Privacy Policy</a> | <a href="http://tools.google.com/dlpage/gaoptout?hl=it" rel="external">Opt Out</a></p>

<p><b>ShareThis</b><br />
Il servizio ShareThis viene utilizzato per facilitare la condivisione dei contenuti sulle più comuni piattaforme social.<br />
<a href="http://www.sharethis.com/legal/privacy/" rel="external">Privacy Policy</a> | <a href="http://www.sharethis.com/legal/privacy/" rel="external">Opt Out</a></p>

<p><b>Disqus</b><br />
Il servizio viene utilizzato per facilitare e migliorare la gestione dei commenti ai contenuti.<br />
<a href="https://help.disqus.com/customer/portal/articles/466259-privacy-policy" rel="external">Privacy Policy</a> | <a href="https://help.disqus.com/customer/portal/articles/1657951" rel="external">Opt Out</a></p>

<p><b>Vimeo</b><br />
Il popolare servizio di streaming video utilizza i cookie per ottimizzare la fruizione dei suoi servizi, e il alcuni casi il Sito può includere video Vimeo.<br />
<a href="https://vimeo.com/cookie_policy" rel="external">Cookie Policy</a></p>

<p><b>Bottoni Social</b><br />
I bottoni social sono bottoni che permettono di rendere più immediata ed agevole la condivisione dei contenuti sulle più comuni piattaforme social. Qui di seguito i dettagli dei principali servizi:</p>\

<p><b>Pulsante +1 e widget sociali di Google+</b> (Google Inc.)<br />
Il pulsante +1 e i widget sociali di Google+ (tra cui i commenti) sono servizi di interazione con il social network Google+, forniti da Google Inc.<br />
Dati personali raccolti: Cookie e Dati di utilizzo.<br />
Luogo del trattamento : USA - <a href="http://www.google.com/intl/it/policies/privacy/" rel="external">Privacy Policy</a></p>

<p><b>Pulsante "Mi Piace" e widget sociali di Facebook</b> (Facebook, Inc.)<br />
Il pulsante "Mi Piace" e i widget sociali di Facebook sono servizi di interazione con il social network Facebook, forniti da Facebook, Inc.<br />
Dati personali raccolti: Cookie e Dati di utilizzo.<br />
Luogo del trattamento : USA - <a href="http://www.facebook.com/privacy/explanation.php" rel="external">Privacy Policy</a></p>

<p><b>Pulsante Tweet e widget sociali di Twitter</b> (Twitter, Inc.)<br />
Il pulsante Tweet e i widget sociali di Twitter sono servizi di interazione con il social network Twitter, forniti da Twitter, Inc.<br />
Dati personali raccolti: Cookie e Dati di utilizzo.<br />
Luogo del trattamento : USA - <a href="http://twitter.com/privacy" rel="external">Privacy Policy</a></p>

<p><b>Pulsante e widget sociali di Linkedin</b> (Linkedin Corp.)<br />
Il pulsante e i widget sociali di Linkedin sono servizi di interazione con il social network Linkedin, forniti da Linkedin Inc.<br />
Dati personali raccolti: Cookie e Dati di navigazione ed utilizzo.<br />
Luogo del Trattamento: USA - <a href="http://www.linkedin.com/static?key=privacy_policy&trk=hb_ft_priv" rel="external">Privacy Policy</a></p>

<p><b>Cookie Script</b><br />
Il Sito utilizza il servizio Cookie Script per l''accettazione dell''utilizzo dei cookies. Se acconsenti all''utilizzo dei cookies, un ulteriore cookie tecnico di nome cookiescriptaccept verrà scritto per ricordare in futuro la tua scelta.<br />
<a href="https://cookie-script.com/privacy-policy-and-disclaimer.html" rel="external">Privacy Policy</a></p>

<p>8 - Questa pagina è raggiungibile mediante un link presente in tutte le pagine del Sito.</p>

<p>9 - Negando il consenso all''utilizzo dei cookie, nessun cookie verrà scritto sul dispositivo dell''utente, eccetto il cookie tecnico di sessione. Sarà ancora possibile navigare il Sito, ma alcune parti di esso potrebbero non funzionare correttamente.</p>

<p> </p>

<p><b>Ma... cosa sono i cookie?</b></p>

<p>I cookie sono file o pacchetti di dati che possono venire salvati sul computer dell''utente (o altro dispositivo abilitato alla navigazione su internet, per esempio smartphone o tablet) quando visita un sito web. Di solito un cookie contiene il nome del sito internet dal quale il cookie stesso proviene, la durata del cookie (ovvero l''indicazione del tempo per il quale il cookie rimarrà memorizzato sul dispositivo), ed un contenuto (numero, stringa, etc.), che gli permette di svolgere la sua funzione.<br />
Per maggiori informazioni visita il sito in lingua inglese <a href="http://aboutcookies.org/." rel="external">aboutcookies.org</a>.</p>

<p> </p>

<p><b>Per cosa si usano i cookie?</b></p>

<p>Si utilizzano i cookie per rendere la navigazione più semplice e per meglio adattare il sito web ai bisogni dell''utente. I cookie possono anche venire usati per aiutare a velocizzare le future esperienze ed attività dell''utente su altri siti web, e si usano per compilare statistiche anonime aggregate che consentono di capire come gli utenti usano i siti in modo da aiutare a migliorare la struttura ed i contenuti di questi siti.</p>

<p> </p>

<p><b>I diversi tipi di cookie</b></p>

<p><b>Cookies Tecnici</b>: sono i cookie che servono a effettuare la navigazione o a fornire un servizio richiesto dall''utente. Non vengono utilizzati per scopi ulteriori e sono normalmente installati direttamente dal gestore del sito web che si sta novigando. Senza il ricorso a tali cookie, alcune operazioni non potrebbero essere compiute o sarebbero più complesse e/o meno sicure, (ad esempio i cookie che consentono di effettuare e mantenere l''identificazione dell''utente nell''ambito della sessione).</p>

<p><b>Cookies di Profilazione</b>: sono i cookie utilizzati per tracciare la navigazione dell''utente in rete e creare profili sui suoi gusti, abitudini, scelte, ecc. Con questi cookie possono essere trasmessi al terminale dell''utente messaggi pubblicitari in linea con le preferenze già manifestate dallo stesso utente nella navigazione online.</p>

<p><b>Cookies di prima parte</b> (first-part cookie) sono i cookie generati e utilizzati direttamente dal soggetto gestore del sito web sul quale l''utente sta navigando.</p>

<p><b>Cookies di terza parte</b> (third-part cookie), sono i cookie generati e gestiti da soggetti diversi dal gestore del sito web sul quale l''utente sta navigando (in forza, di regola, di un contratto tra il titolare del sito web e la terza parte)</p>

<p><b>Cookies di Sessione</b> e <b>Cookies Persistenti</b>:<br />
mentre la differenza tra un cookie di prima parte e un cookie di terzi riguarda il soggetto che controlla l''invio iniziale del cookie sul tuo dispositivo, la differenza tra un cookie di sessione e un cookie persistente riguarda il diverso lasso di tempo per cui un cookie opera. I cookie di sessione sono cookie che tipicamente durano finchè chiudi il tuo internet browser. Quando finisci la tua sessione browser, il cookie scade. I cookies persistenti, come lo stesso nome indica, sono cookie costanti e continuano ad operare dopo che hai chiuso il tuo browser.</p>

<p> </p>

<p><b>Come posso controllare le gestione dei cookie del mio browser?</b></p>

<p>Tutti i moderni browser offrono la possibilita di controllare le impostazioni di privacy, anche per quello che riguarda l''uso dei cookie. In particolare l''utente potrà intervenire sul comportamento generale del browser nei confronti dei cookie (ad esempio instruendolo a NON accettarli in futuro), visualizzare e/o cancellare i cookie già installati.<br />
<br />
Riportiamo qui di seguito le procedure per accedere a queste impostazioni per i browser più utilizzati:<br />
<br />
<a href="https://support.google.com/chrome/answer/95647?hl=it" rel="external">Chrome</a></p>

<p><a href="http://windows.microsoft.com/it-it/windows-vista/block-or-allow-cookies" rel="external">Internet Explorer</a></p>

<p><a href="https://support.mozilla.org/it/kb/Gestione%20dei%20cookie" rel="external">Firefox</a></p>

<p><a href="http://www.opera.com/help/tutorials/security/privacy/" rel="external">Opera</a></p>

<p><a href="https://support.apple.com/kb/PH17191?locale=en_US" rel="external">Safari 6/7</a></p>

<p><a href="https://support.apple.com/kb/PH19214?locale=en_US" rel="external">Safari 8</a></p>

<p><a href="https://support.apple.com/en-us/HT201265" rel="external">Safari mobile</a></p>', NULL, 0, 1, 0, 0, NULL, 0, NULL, NULL);

-- Structure query
ALTER TABLE sys_conf ADD query_cache TINYINT NOT NULL 
	CONSTRAINT DF_sys_conf_query_cache DEFAULT '0';
ALTER TABLE sys_conf ADD query_cache_time SMALLINT NULL;

ALTER TABLE page_opt ALTER COLUMN newsletter_tpl_code TEXT;

