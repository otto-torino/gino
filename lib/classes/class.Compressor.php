<?php
/**
 * @file class.Compressor.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Compressor,  Gino.JSMin e Gino.JSMinException
 * 
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Classe per la compressione di css e js (merge e minify)
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Compressor {

    private $_css = array(),
            $_js = array();

    /**
     * @brief Costruttore
     * @param array $params Array associativo di parametri
     *                      - css: path o array di path relativi di css
     *                      - js: path o array di path relativi di js
     * @return istanza di Gino.Compressor
     */
    function __construct($params = array()) {
        // paths
        foreach(array('css', 'js') as $t) {
            if(isset($params[$t])) {
                if(!is_array($params[$t])) {
                    $this->{'_'.$t} = array($params[$t]);
                }
                else {
                    $this->{'_'.$t} = $params[$t];
                }
            }
        }
    }

    /**
     * @brief Aggiunge nuovi path css
     * @return void
     */
    public function addCss($paths) {
        if(!is_array($paths)) {
            $paths = array($paths);
        }
        $this->_css = array_merge($this->_css, $paths);
    }

    /**
     * @brief Aggiunge nuovi path js
     * @return void
     */
    public function addJs($paths) {
        if(!is_array($paths)) {
            $paths = array($paths);
        }
        $this->_js = array_merge($this->_js, $paths);
    }

    /**
     * @brief Merge di script js in un unico file
     * @param array $options array associativo di opzioni
     *                       - minify: eseguire il minify dei file oppure no
     * @return path del file unificato
     */
    public function mergeJs($options = array()) {
        $minify = gOpt('minify', $options, true);
        $paths_string = md5(implode('', $this->_js));
        if($this->shouldUpdate('js', $paths_string, $this->_js)) {
            $script = '';
            foreach($this->_js as $js_rel_path) {
                $js_path = absolutePath($js_rel_path);
                $script .= $minify ? $this->minifyJs(file_get_contents($js_path)) : file_get_contents($js_path);
            }
            file_put_contents($this->filePath('js', $paths_string), $script);
        }

        return relativePath($this->filePath('js', $paths_string));
    }

    /**
     * @brief Esegue il minify del contenuto js
     * @param string $content contenuto js
     * @see JSMin
     * @return contenuto minificato
     */
    private function minifyJs($content) {
        return JSMin::minify($content);
    }

    /**
     * @brief Merge di stylesheet in un unico file
     * @param array $options array associativo di opzioni
     *                       - minify: eseguire il minify dei file oppure no
     * @return path del file unificato
     */
    public function mergeCss($options = array()) {
        $minify = gOpt('minify', $options, true);
        $paths_string = md5(implode('', $this->_css));
        if($this->shouldUpdate('css', $paths_string, $this->_css)) {
            $style = '';
            foreach($this->_css as $css_rel_path) {
                $css_path = absolutePath($css_rel_path);
                $style .= $minify 
                    ? $this->minifyCss($this->moveCss($css_path, $this->filePath('css', $paths_string), file_get_contents($css_path))) 
                    : $this->moveCss($css_path, $this->filePath('css', $paths_string), file_get_contents($css_path));
            }
            file_put_contents($this->filePath('css', $paths_string), $style);
        }

        return relativePath($this->filePath('css', $paths_string));
    }

    /**
     * @brief Verifica se il file compresso deve essere ricreato
     * @return vero o falso
     */
    private function shouldUpdate($type, $paths_string, $paths) {

        $cache_path = $this->filePath($type, $paths_string);
        if(!file_exists($cache_path)) return true;

        $ref_mtime = 0;
        foreach($paths as $path) {
            $abs_path = absolutePath($path);
            $ref_mtime = $ref_mtime ? max($ref_mtime, filemtime($abs_path)) : filemtime($abs_path);
        }

        return $ref_mtime > filemtime($cache_path);

    }

    /**
     * @brief Percorso del file compresso
     * @return path
     */
    private function filePath($type, $paths_string) {
        return CACHE_DIR.OS.$type.OS.$paths_string.'.min.'.$type;
    }

    /**
     * @brief Modifica i path all'interno dei css per rispecchiare il cambio di directory del css
     * @param string $source path del css sorgente
     * @param string $destination path del css di destinazione
     * @param string $content contenuto del file sorgente
     * @return contenuto con i path aggiornati
     */
    private function moveCss($source, $destination, $content) {
        $relative_regexes = array(
            // url(xxx)
            '#url\((?P<quotes>["\'])?(?P<path>(?!(["\']?(data|https?):)).+?)(?(quotes)(?P=quotes))\)#ix',
            '#@import\s+(?P<quotes>["\'])(?P<path>(?!(["\']?(data|https?):)).+?)(?P=quotes)#ix'
        );

        // find all relative urls in css
        $matches = array();
        foreach($relative_regexes as $relative_regex) {
            if(preg_match_all($relative_regex, $content, $regex_matches, PREG_SET_ORDER)) {
                $matches = array_merge($matches, $regex_matches);
            }
        }

        $search = array();
        $replace = array();

        // loop all urls
        foreach($matches as $match) {
            // determine if it's a url() or an @import match
            $type = (strpos($match[0], '@import') === 0 ? 'import' : 'url');

            // fix relative url
            $url = $this->convertRelativePath($match['path'], dirname($source), dirname($destination));

            // build replacement
            $search[] = $match[0];
            if ($type == 'url') {
                $replace[] = 'url(' . $url . ')';
            } elseif ($type == 'import') {
                $replace[] = '@import "' . $url . '"';
            }
        }

        // replace urls
        $content = str_replace($search, $replace, $content);

        return $content;
    }

    /**
     * @brief Converte un path relativo ad una folder in un path relativo ad un'altra folder
     * @param string $path percorso da convertire
     * @param string $from folder attuale
     * @param string $to folder finale
     * @return path convertito
     */
    private function convertRelativePath($path, $from, $to) {
        $from = $from ? realpath($from) : '';
        $to = $to ? realpath($to) : '';

        // make sure we're dealing with directories
        $from = @is_file($from) ? dirname($from) : $from;
        $to = @is_file($to) ? dirname($to) : $to;

        // deal with different operating systems' directory structure
        $path = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $path), '/');
        $from = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $from), '/');
        $to = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $to), '/');

        // if we're not dealing with a relative path, just return absolute
        if(strpos($path, '/') === 0) {
            return $path;
        }

        // get full path to file referenced from $from
        $path = $from . '/' . $path;

        /*
         * Example:
         * $path = /home/forkcms/frontend/cache/compiled_templates/../../core/layout/css/../images/img.gif
         * $to = /home/forkcms/frontend/cache/minified_css
         */

        // normalize paths
        // see http://www.regular-expressions.info/lookaround.html for (?<!\.\.) meaning
        do {
            list($path, $to) = preg_replace('#[^/]+(?<!\.\.)/\.\./#', '', array($path, $to), -1, $count);
        } while ($count);

        /*
         * Example:
         * $path = /home/forkcms/frontend/core/layout/images/img.gif
         * $to = /home/forkcms/frontend/cache/minified_css
         */

        $path = explode('/', $path);
        $to = explode('/', $to);

        // compare paths & strip identical ancestors
        foreach ($path as $i => $chunk) {
            if (isset($to[$i]) && $path[$i] == $to[$i]) {
                unset($path[$i], $to[$i]);
            } else {
                break;
            }
        }

        /*
         * At this point:
         * $path = array('core', 'layout', 'images', 'img.gif')
         * $to = array('cache', 'minified_css')
         */

        $path = implode('/', $path);

        // add .. for every directory that needs to be traversed for new path
        $to = str_repeat('../', count($to));

        /*
         * At this point:
         * $path = core/layout/images/img.gif
         * $to = ../../
         */

        // Tada!
        return $to . $path;
    }

    /**
     * @brief Esegue il minify del contenuto css
     * @param string $content contenuto css
     * @return contenuto minificato
     */
    private function minifyCss($content) {
        // Remove comments
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);

        // Remove space after colons
        $content = str_replace(': ', ':', $content);

        // Remove newlines, tabs
        $content = str_replace(array("\r\n", "\r", "\n", "\t"), '', $content);

        return $content;
    }

}

/**
 * @brief PHP implementation of Douglas Crockford's JSMin.
 *
 * This is pretty much a direct port of jsmin.c to PHP with just a few
 * PHP-specific performance tweaks. Also, whereas jsmin.c reads from stdin and
 * outputs to stdout, this library accepts a string as input and returns another
 * string as output.
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @copyright 2012 Adam Goforth <aag@adamgoforth.com> (Updates)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.2 (2012-05-01)
 * @link https://github.com/rgrove/jsmin-php
 */
class JSMin {
  const ORD_LF            = 10;
  const ORD_SPACE         = 32;
  const ACTION_KEEP_A     = 1;
  const ACTION_DELETE_A   = 2;
  const ACTION_DELETE_A_B = 3;

  protected $a           = '';
  protected $b           = '';
  protected $input       = '';
  protected $inputIndex  = 0;
  protected $inputLength = 0;
  protected $lookAhead   = null;
  protected $output      = '';

  // -- Public Static Methods --------------------------------------------------

  /**
   * Minify Javascript
   *
   * @uses __construct()
   * @uses min()
   * @param string $js Javascript to be minified
   * @return string
   */
  public static function minify($js) {
    $jsmin = new JSMin($js);
    return $jsmin->min();
  }

  // -- Public Instance Methods ------------------------------------------------

  /**
   * Constructor
   *
   * @param string $input Javascript to be minified
   */
  public function __construct($input) {
    $this->input       = str_replace("\r\n", "\n", $input);
    $this->inputLength = strlen($this->input);
  }

  // -- Protected Instance Methods ---------------------------------------------

  /**
   * Action -- do something! What to do is determined by the $command argument.
   *
   * action treats a string as a single character. Wow!
   * action recognizes a regular expression if it is preceded by ( or , or =.
   *
   * @uses next()
   * @uses get()
   * @throws JSMinException If parser errors are found:
   *         - Unterminated string literal
   *         - Unterminated regular expression set in regex literal
   *         - Unterminated regular expression literal
   * @param int $command One of class constants:
   *      ACTION_KEEP_A      Output A. Copy B to A. Get the next B.
   *      ACTION_DELETE_A    Copy B to A. Get the next B. (Delete A).
   *      ACTION_DELETE_A_B  Get the next B. (Delete B).
  */
  protected function action($command) {
    switch($command) {
      case self::ACTION_KEEP_A:
        $this->output .= $this->a;

      case self::ACTION_DELETE_A:
        $this->a = $this->b;

        if ($this->a === "'" || $this->a === '"') {
          for (;;) {
            $this->output .= $this->a;
            $this->a       = $this->get();

            if ($this->a === $this->b) {
              break;
            }

            if (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated string literal.');
            }

            if ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            }
          }
        }

      case self::ACTION_DELETE_A_B:
        $this->b = $this->next();

        if ($this->b === '/' && (
            $this->a === '(' || $this->a === ',' || $this->a === '=' ||
            $this->a === ':' || $this->a === '[' || $this->a === '!' ||
            $this->a === '&' || $this->a === '|' || $this->a === '?' ||
            $this->a === '{' || $this->a === '}' || $this->a === ';' ||
            $this->a === "\n" )) {

          $this->output .= $this->a . $this->b;

          for (;;) {
            $this->a = $this->get();

            if ($this->a === '[') {
              /*
                inside a regex [...] set, which MAY contain a '/' itself. Example: mootools Form.Validator near line 460:
                  return Form.Validator.getValidator('IsEmpty').test(element) || (/^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]\.?){0,63}[a-z0-9!#$%&'*+/=?^_`{|}~-]@(?:(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\])$/i).test(element.get('value'));
              */
              for (;;) {
                $this->output .= $this->a;
                $this->a = $this->get();

                if ($this->a === ']') {
                    break;
                } elseif ($this->a === '\\') {
                  $this->output .= $this->a;
                  $this->a       = $this->get();
                } elseif (ord($this->a) <= self::ORD_LF) {
                  throw new JSMinException('Unterminated regular expression set in regex literal.');
                }
              }
            } elseif ($this->a === '/') {
              break;
            } elseif ($this->a === '\\') {
              $this->output .= $this->a;
              $this->a       = $this->get();
            } elseif (ord($this->a) <= self::ORD_LF) {
              throw new JSMinException('Unterminated regular expression literal.');
            }

            $this->output .= $this->a;
          }

          $this->b = $this->next();
        }
    }
  }

  /**
   * Get next char. Convert ctrl char to space.
   *
   * @return string|null
   */
  protected function get() {
    $c = $this->lookAhead;
    $this->lookAhead = null;

    if ($c === null) {
      if ($this->inputIndex < $this->inputLength) {
        $c = substr($this->input, $this->inputIndex, 1);
        $this->inputIndex += 1;
      } else {
        $c = null;
      }
    }

    if ($c === "\r") {
      return "\n";
    }

    if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
      return $c;
    }

    return ' ';
  }

  /**
   * Is $c a letter, digit, underscore, dollar sign, or non-ASCII character.
   *
   * @return bool
   */
  protected function isAlphaNum($c) {
    return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }

  /**
   * Perform minification, return result
   *
   * @uses action()
   * @uses isAlphaNum()
   * @uses get()
   * @uses peek()
   * @return string
   */
  protected function min() {
    if (0 == strncmp($this->peek(), "\xef", 1)) {
        $this->get();
        $this->get();
        $this->get();
    } 

    $this->a = "\n";
    $this->action(self::ACTION_DELETE_A_B);

    while ($this->a !== null) {
      switch ($this->a) {
        case ' ':
          if ($this->isAlphaNum($this->b)) {
            $this->action(self::ACTION_KEEP_A);
          } else {
            $this->action(self::ACTION_DELETE_A);
          }
          break;

        case "\n":
          switch ($this->b) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
            case '!':
            case '~':
              $this->action(self::ACTION_KEEP_A);
              break;

            case ' ':
              $this->action(self::ACTION_DELETE_A_B);
              break;

            default:
              if ($this->isAlphaNum($this->b)) {
                $this->action(self::ACTION_KEEP_A);
              }
              else {
                $this->action(self::ACTION_DELETE_A);
              }
          }
          break;

        default:
          switch ($this->b) {
            case ' ':
              if ($this->isAlphaNum($this->a)) {
                $this->action(self::ACTION_KEEP_A);
                break;
              }

              $this->action(self::ACTION_DELETE_A_B);
              break;

            case "\n":
              switch ($this->a) {
                case '}':
                case ']':
                case ')':
                case '+':
                case '-':
                case '"':
                case "'":
                  $this->action(self::ACTION_KEEP_A);
                  break;

                default:
                  if ($this->isAlphaNum($this->a)) {
                    $this->action(self::ACTION_KEEP_A);
                  }
                  else {
                    $this->action(self::ACTION_DELETE_A_B);
                  }
              }
              break;

            default:
              $this->action(self::ACTION_KEEP_A);
              break;
          }
      }
    }

    return $this->output;
  }

  /**
   * Get the next character, skipping over comments. peek() is used to see
   *  if a '/' is followed by a '/' or '*'.
   *
   * @uses get()
   * @uses peek()
   * @throws JSMinException On unterminated comment.
   * @return string
   */
  protected function next() {
    $c = $this->get();

    if ($c === '/') {
      switch($this->peek()) {
        case '/':
          for (;;) {
            $c = $this->get();

            if (ord($c) <= self::ORD_LF) {
              return $c;
            }
          }

        case '*':
          $this->get();

          for (;;) {
            switch($this->get()) {
              case '*':
                if ($this->peek() === '/') {
                  $this->get();
                  return ' ';
                }
                break;

              case null:
                throw new JSMinException('Unterminated comment.');
            }
          }

        default:
          return $c;
      }
    }

    return $c;
  }

  /**
   * Get next char. If is ctrl character, translate to a space or newline.
   *
   * @uses get()
   * @return string|null
   */
  protected function peek() {
    $this->lookAhead = $this->get();
    return $this->lookAhead;
  }
}

/**
 * @brief JSMinException
 */
class JSMinException extends Exception {}
