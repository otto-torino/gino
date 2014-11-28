<?php
/**
 * @file func.file.php
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
 * @return array di nomi di file
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
 * @return stream del file o FALSE se il file non si apre in lettura
 */
function download($full_path)
{
    if($fp = fopen($full_path, "r"))
    {
        $fsize = filesize($full_path);
        $path_parts = pathinfo($full_path);

        $response = Loader::load('http/ResponseFile', array($full_path, 'application/download', $path_parts["basename"]), '\Gino\Http\\');
        $response->setDispositionType('Attachment');
        $response->setHeaders(array(
            'Pragma' => 'public',
            'Expires' => '0',
            'Content-Description' => 'File Transfer',
            'Content-length' => $fsize,
            'Cache-control' => 'private'
        ));
    }
    else {
        $response = Loader::load('http/ResponseNotFound', array(), '\Gino\Http\\');
    }

    return $response;
}

/**
 * @brief Controlla le estensioni dei file
 *
 * @description Verifica se il file ha una estensione valida, ovvero presente nell'elenco delle estensioni.
 *
 * @param string $filename nome del file
 * @param array $extensions elenco delle estensioni valide
 * @return TRUE se $extension è vuoto o se il file ha un'estensione valida
 *
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

        if($count > 0) return TRUE; else return FALSE;
    }
    else return TRUE;
}

/**
 * @brief Ricava il nome del file senza l'estensione
 * @param string $filename nome del file
 * @return nome file senza estensione
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
function deleteFileDir($dir, $delete_dir = TRUE){

    if(is_dir($dir))
    {
        if(substr($dir, -1) != '/') $dir .= OS;    // Append slash if necessary

        if($dh = opendir($dir))
        {
            while(($file = readdir($dh)) !== FALSE)
            {
                if($file == "." || $file == "..") continue;

                if(is_file($dir.$file)) unlink($dir.$file);
                else \Gino\deleteFileDir($dir.$file, TRUE);
            }

            if($delete_dir)
            {
                closedir($dh);
                rmdir($dir);
            }
        }
    }

    return TRUE;
}

/**
 * @brief Estensione di un file
 *
 * @param string $filename nome del file
 * @return estensione
 */
function extensionFile($filename){
    $extension = strtolower(str_replace('.','',strrchr($filename, '.')));
    return $extension;
}

/**
 * @brief Controlla se l'estensione di un file è valida
 *
 * @param string $filename nome del file
 * @param array $extensions elenco dei formati di file permessi
 * @return TRUE se l'estensione è compresa in quelle date, FALSE altrimenti
 */
function verifyExtension($filename, $extensions){

    $ext = $this->extensionFile($filename);

    if(sizeof($extensions) > 0 AND !empty($ext))
    {
        if(in_array($ext, $extensions)) return TRUE; else return FALSE;
    }
    else return FALSE;
}
