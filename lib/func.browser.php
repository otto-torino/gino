<?php
/**
 * @file func.browser.php
 * @brief Racchiude funzioni per il detect di browser, piattaforma etc...
 *
 * @description Funzione originale di Linesh Jose, http://lineshjose.com
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author Linesh Jose lineshjose@gmail.com
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Lista di browser popolari
 * @return array
 */
function browsers(){
    return array(
        0 =>'Avant Browser','Arora', 'Flock', 'Konqueror','OmniWeb','Phoenix','Firebird','Mobile Explorer','Opera Mini','Netscape',
            'Iceweasel','KMLite', 'Midori', 'SeaMonkey', 'Lynx', 'Fluid', 'chimera', 'NokiaBrowser',
            'Firefox','Chrome','MSIE','Internet Explorer','Opera','Safari','Mozilla','trident'
    );
}

/**
 * @brief Lista di web robots popolari
 * @return array
 */
function robots(){
    return array(
         0 => 'Googlebot', 'Googlebot-Image', 'MSNBot', 'Yahoo! Slurp', 'Yahoo', 'AskJeeves','FastCrawler','InfoSeek Robot 1.0', 'Lycos',
              'YandexBot','YahooSeeker'
    );
}

/**
 * @brief Lista di piattaforme popolari
 * @return array
 */
function platforms(){
    return array(
        0 => 'iPad', 'iPhone', 'iPod', 'Mac OS X', 'Macintosh', 'Power PC Mac', 'Windows', 'Windows CE',
             'Symbian', 'SymbianOS', 'Symbian S60', 'Ubuntu', 'Debian', 'NetBSD', 'GNU/Linux', 'OpenBSD', 'Android', 'Linux',
             'Mobile','Tablet',
    );
}

/**
 * @brief Informazioni browser
 * @param string $arg proprietà da ritornare (invece di un array completo)
 * @param string $agent HTTP AGENT, default $_SERVER['HTTP_USER_AGENT']
 * @return informazioni browser, una sola proprietà o un array (agent, name, version, is_bot, platform)
 */
function get_browser_info($arg='',$agent='')
{
    if(empty($agent) ) {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    }
    // browser name
    $name = '';
    foreach( browsers() as $key){
        if(strpos($agent, strtolower(trim($key)))) {
            $name= trim($key);
            break;
        }
    }

    // robot name
    foreach(robots() as $key){
        if (preg_match("|".preg_quote(strtolower(trim($key)))."|i", $agent)){
            $is_bot = TRUE;
            $name= trim($key);
            break;
        }
        else{
            $is_bot = false;
            continue;
        }
    }

    $known = array('version',strtolower($name), 'other');
    $pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (preg_match_all($pattern,$agent, $matches))
    {
        if (count($matches['browser'])>0)
        {
            if (strripos($agent,"version") < strripos($agent,strtolower($name)) ){
                    $version= $matches['version'][0];
            }
            else {
                    $version= $matches['version'][1];
            }
        }
        else {
                $version=0;
        }
        if ($version==null || $version=="") { $version="?"; }
        $version = (int) round($version);
    }

    // platform
    $platform = '';
    foreach(platforms() as $key){
        if (preg_match("|".preg_quote(trim($key))."|i", $agent)) {
            $platform = trim($key);
            break;
        }
    }

    // browser info
    $browser['agent']=$agent;
    if($name=='trident') {
            $browser['name']='Internet Explorer';
            $browser['version']='11';
    }
    elseif(empty($name)) {
            $browser['name']='Unknown';
            $browser['version']=0;        
    }
    else {
            $browser['name']=$name;
            $browser['version']=$version;
    }
    $browser['is_bot']=$is_bot;
    $browser['platform']=$platform;

    if($arg){
        return $browser[$arg];
    }
    else{
        return $browser;
    }
}

/**
 * @brief Verifica se il borwser è quello dato come argomento
 * @param string $name nome browser
 * @return bool
 */
function is_browser($name){
    $name=strtolower(trim($name));
    $curr_brws=strtolower(get_browser_info('name'));
    if($curr_brws==$name) {
        return TRUE;
    }
    else {
        return FALSE;
    }
}

/**
 * @brief Verifica la versione del browser
 * @param string $version
 * @return bool
 */
function is_browser_version($version){
    $version=strtolower(trim($version));
    $curr_version=strtolower(get_browser_info('version'));
    if($version==$curr_version) {
        return TRUE;
    }
    else{
        return FALSE;
    }
}

/**
 * @brief Verifica la piattaforma del browser
 * @param string $platform
 * @return bool
 */
function is_browser_platform($platform){
    $platform=strtolower(trim($platform));
    $curr_platform=strtolower(get_browser_info('platform'));
    if($curr_platform==$platform){
        return TRUE;
    }
    elseif( $platform=='ios' && in_array($curr_platform, array('iphone','ipod','ipad'))){
        return TRUE;
    }
    else{
        return FALSE;
    }
}

/**
 * @brief Verifica se il client è un robot
 * @return bool
 */
function is_robot(){
    if(get_browser_info('is_bot')){
        return TRUE;
    }
    else {
        return TRUE;
    }
}
