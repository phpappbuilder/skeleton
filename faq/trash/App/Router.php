<?
namespace Centurion\Core\App;

class Router extends \Klein\Klein
{
  public $temp_path = 'core/temp';
  public $route_dir = 'app';
  public $route_map;
  private $variable_tmp;


  public function recurs_ive ($dir)
  {
    $files = [];
    if ($handle = opendir($dir))
      {
        while (false !== ($item = readdir($handle)))
          {
            if (is_file($dir.'/'.$item))
              {
                $files[] = $dir.'/'.$item;
                if ($item == 'RouterBundle.php')
                  {

                    $bundle = require ($dir.'/'.$item);

                    $this -> add_c_route($bundle);

                  }
              }
              elseif (is_dir($dir.'/'.$item) && ($item != ".") && ($item != "..")){
                  $files = array_merge($files, $this -> recurs_ive($dir.'/'.$item));
              }
          }
          closedir($handle);
      }
      return $files;
  }

  public function add_c_route ($bundle , $add = true)
    {

      for ($i=0;$i<count($bundle);$i++)
        {
          $this -> variable_tmp = $bundle[$i]['module'];
          $this -> respond($bundle[$i]['method'], $bundle[$i]['route'], function ($request, $response, $service) {
            require ($this -> variable_tmp);
          });
          if ($add == true) {$this -> route_map[] = $bundle[$i];}
        }
    }

  public function add_route_modules($dir = '')
    {
      if ($dir == ''){$dir = $this -> route_dir;}
      if (is_file($this -> temp_path.'/route_map.json'))
        {
          $this -> route_map = json_decode(file_get_contents($this -> temp_path.'/route_map.json') , true);
          $this -> add_c_route($this -> route_map , false);
        }
        else
        {
          $this -> recurs_ive($dir);
          file_put_contents($this -> temp_path . '/route_map.json', json_encode($this -> route_map));
        }
    }

  public function update_routes($dir = '')
    {
      if ($dir == ''){$dir = $this -> route_dir;}
      if (is_file($this ->  temp_path.'/route_map.json')) {unlink($this ->  temp_path.'/route_map.json');}
      $this -> add_route_modules($dir);
    }

  public function thor(){ print_r($this -> routes); }
}
