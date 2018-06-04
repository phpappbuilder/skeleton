<?
namespace Centurion\Core\Config;

class Manager
{

  private $temp = 'core/var/ConfigBundles.php';


  private function recurs_ive ($dir = '') {
    $files = [];
    if ($handle = opendir($dir))
      {
        while (false !== ($item = readdir($handle)))
          {
            if (is_file($dir.'/'.$item))
              {

                if ($item == 'ConfigBundle.php')
                  {$files[] = $dir.'/'.$item;
                    $conf = require ($dir.'/'.$item);

                      $this -> addPath($conf['path'] , $conf['path_i18n']);

                      foreach ($conf['var'] as $num => $var) {
                        $this -> addConfig ($conf['path'] , $conf['var']);
                      }

                  }
              }
              elseif (is_dir($dir.'/'.$item) && ($item != ".") && ($item != "..") && ($item != "")){
                  $files = array_merge($files, $this -> recurs_ive($dir.'/'.$item));
              }
          }
          closedir($handle);
      }
      return $files;
  }

  private function saveCache( $config ) {
		$file = $this->temp;
		$code = "<?php \n return " . var_export($config, true) . ";\n";
    @unlink($file);
		file_put_contents($file, $code);
		if (function_exists('opcache_invalidate')) {
			@opcache_invalidate($file, true); // @ can be restricted
		}
	}

  public function clearCache() {
    $this -> saveCache( [] );
  }

  public function updateCache($dir) {
    $this -> clearCache();
    $this -> recurs_ive($dir);
  }

  public function addPath ($path, $i18n='') {
    if ($i18n == '') {$i18n = $path;}
    $extend = [$path => [ "path_i18n" => $i18n , "var" => []]];
    $cache = require($this -> temp);
    $cache = array_merge($extend , $cache);
    $this->saveCache($cache);
  }

  public function addConfig ($path = '', $arr) {
    $this -> addPath ($path);
    $cache = require($this -> temp);

    $cache[$path]['var'] = array_merge($arr , $cache[$path]['var']);
    $this -> saveCache($cache);
  }

  public function getConfig ( $path , $var )
    {
      $cache = require($this -> temp);
      return $cache[$path]['var'][$var]['value'];
    }

  public function updateConfig ( $path , $var , $value)
    {
      $cache = require($this -> temp);
      $cache[$path]['var'][$var]['value'] = $value;
      $this -> saveCache($cache);
    }
}
