<?php
/**
 * @file func.filephp
 * @brief Racchiude le funzioni generali di manipolazione file e directory utilizzate da gino
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Lista files contenuti in una directory
 * 
 * @param string $dir percorso della directory (se @a dir è un percorso relativo, verrà aperta la directory relativa alla directory corrente)
 * @return array
 */
function searchNameFile($dir){
    $filenames = array();
    if(is_dir($dir))
    {
        $dp = opendir($dir);
        while($file = readdir($dp))
        {
            if($file != "." AND $file != "..")
            {
                $filenames[] = $file;
            }
        }
    }

    return $filenames;
}

/**
 * @brief Forza il download di un file
 * 
 * @param string $full_path percorso del file
 * @return stream del file o falso se il file non si apre in lettura
 */
function download($full_path)
{
    if($fp = fopen($full_path, "r"))
    {
        $fsize = filesize($full_path);
        $path_parts = pathinfo($full_path);
        $extension = strtolower($path_parts["extension"]);

        header("Pragma: public");
        header('Expires: 0');
        header('Content-Description: File Transfer');
        header("Content-type: application/download");
        header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"");
        header("Content-length: ".$fsize);
        header("Cache-control: private");

        ob_clean();
        flush();

        @readfile($full_path);
        fclose($fp);
    }
    else {
        return false;
    }
}

/**
 * @brief Controlla le estensioni dei file
 * 
 * @description Verifica se il file ha una estensione valida, ovvero presente nell'elenco delle estensioni.
 *
 * @param string $filename nome del file
 * @param array $extensions elenco delle estensioni valide
 * @return boolean
 * 
 * se $extensions è vuoto => true
 */
function extension($filename, $extensions){
    $ext = str_replace('.','',strrchr($filename, '.'));
    $count = 0;
    if(is_array($extensions) AND sizeof($extensions) > 0)
    {
        foreach($extensions AS $value)
        {
            if(strtolower($ext) == strtolower($value))
            $count++;
        }

        if($count > 0) return true; else return false;
    }
    else return true;
}

/**
 * Ricava il nome del file senza l'estensione
 * @param string $filename nome del file
 * @return string
 */
function baseFileName($filename) {
    return substr($filename, 0, strrpos($filename, '.'));
}

/**
 * @brief Elimina ricorsivamente i file e le directory
 *
 * @param string $dir percorso assoluto alla directory
 * @param boolean $delete_dir per eliminare o meno le directory
 * @return void
 */
function deleteFileDir($dir, $delete_dir=true){

    if(is_dir($dir))
    {
        if(substr($dir, -1) != '/') $dir .= OS;    // Append slash if necessary

        if($dh = opendir($dir))
        {
            while(($file = readdir($dh)) !== FALSE)
            {
                if($file == "." || $file == "..") continue;

                if(is_file($dir.$file)) unlink($dir.$file);
                else $this->deleteFileDir($dir.$file, TRUE);
            }

            if($delete_dir)
            {
                closedir($dh);
                rmdir($dir);
            }
        }
    }
}

/**
 * Elimina il file indicato
 * 
 * Viene richiamato dalla classe mFile.
 *
 * @param string $path_to_file percorso assoluto al file
 * @param string $home (proprietà @a $_home)
 * @param string $redirect (class-function)
 * @param string $param_link parametri url (es. id=3&ref=12&)
 * @return boolean
 */
function deleteFile($path_to_file, $home, $redirect, $param_link){

    if(is_file($path_to_file))
    {
        if(!@unlink($path_to_file))
        {
            if(!empty($redirect)) EvtHandler::HttpCall($home, $redirect, $param_link.'error=17');
            else return FALSE;
        }
    }
    return TRUE;
}

/**
 * Nome dell'estensione di un file
 *
 * @param string $filename nome del file
 * @return string
 */
function extensionFile($filename){
    $extension = strtolower(str_replace('.','',strrchr($filename, '.')));
    return $extension;
}

/**
 * Controlla se l'estensione di un file è valida
 *
 * @param string $filename nome del file
 * @param array $extensions elenco dei formati di file permessi
 * @return boolean
 */
function verifyExtension($filename, $extensions){

    $ext = $this->extensionFile($filename);

    if(sizeof($extensions) > 0 AND !empty($ext))
    {
        if(in_array($ext, $extensions)) return TRUE; else return FALSE;
    }
    else return FALSE;
}
