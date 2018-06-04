<?php
namespace Core;

use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\ArrayItem;


class Space
{

    private $temp = 'var/space/';
    private $BundleList = [];
    private $KeyList = [];
    private $CollectionList = [];

    /* Keys */
    static function GetKeys( $path ) {} //возвращает спсиок ключей в пространстве
    static function GetKey( $path ) {} //Возвращает значение ключа
    static function GetValues( $path ) {} //Возвращает все возможные значения ключа
    static function SelectValue( $path , $value ) {} //присваивает ключу значение по id из GetValues


    /* Collections */
    static function GetCollections( $path ) {} //Возвращает список всех коллекций в пространстве
    static function GetCollection( $path ) {} //возвращает коллекцию
    static function ListCollection( $path ) {} // Возврщает коллекцию с названиями и id
    static function CollectionItemStatus( $path , $id , $enabled = true ) {} //Делает видимым или невидимым эллемент коллекции по id


    /* Build */
    //Рекурсивно бегает по папкам и сохраняет все найденные бандлы в $this->BundleList
    private function Iterator( $dir ) {
      $files = [];
      $this -> BundleList = [];
      if ($handle = opendir($dir))
        {
          while (false !== ($item = readdir($handle)))
            {
              if (is_file($dir.'/'.$item))
                {

                  if ($item == 'SpaceBundle.php')
                    {
                      $files[] = $dir.'/'.$item;
                      $this -> BundleList [] = $dir.'/'.$item;
                      $this->RecursiveBundle($dir.'/'.$item);

                    }
                }
                elseif (is_dir($dir.'/'.$item) && ($item != ".") && ($item != "..") && ($item != "")){
                    $files = array_merge($files, $this -> Iterator($dir.'/'.$item));
                }
            }
            closedir($handle);
        }
        return $files;
    }

    //Ищет внутри $file ссылки на другие бандлы и добавляет их в $this->BundleList
    private function RecursiveBundle( $file ) {
        if (is_file($file))
        {
            $bundle = require( $file );
            for ($i=0; $i<count($bundle); $i++)
                {
                    if(isset($bundle[$i]['Space']))
                        {

                            $this -> BundleList [] = $bundle[$i]['Space'];
                            $this -> RecursiveBundle($bundle[$i]['Space']);
                        }
                }
        }
        else{
            return false;
        }
    }

    //Проходится по $this->BundleList И сортирует в key & collection
    private function BundleParser() {
        $bundles = $this->BundleList;
        $count = count($bundles);
        for ($i=0;$i<$count;$i++)
            {
                $bundle = require($bundles[$i]);
                $count_b = count($bundle);
                for ($b=0;$b<$count_b;$b++)
                    {
                        if (!isset($bundle[$b]['Space']))
                            {
                                $path = explode("/",$bundle[$b]['path']);
                                if ($path[0]==='key'){

                                    $this -> KeyList [] = [
                                        "vendor" => $path[1],
                                        "app" => $path[2],
                                        "key" => $path[3],
                                        "file" => $bundles[$i],
                                        "position" => $b
                                    ];

                                } elseif ($path[0]==='collection') {

                                    $this -> CollectionList [] = [
                                        "vendor" => $path[1],
                                        "app" => $path[2],
                                        "collection" => $path[3],
                                        "file" => $bundles[$i],
                                        "position" => $b
                                    ];

                                }

                            }
                    }
            }
    }

    //Вытаскивает из бандала эллемент находящийся в позиции N
    private function PositionParser($file , $position) {
        $code = file_get_contents($file);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($code);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }

        //$dumper = new NodeDumper;
        $str = $ast[0] -> expr -> items[$position];
        $str -> value -> items [] =

        return var_export($ast, true);
    }

    public function test( $file )
    {
        $this -> Iterator( $file );
        $this -> BundleParser();
        return $this ->PositionParser('App/root/core/SpaceBundle.php', 0);
    }
    private function DeletePath( $path ) {} //удаляет из пространств все найденные значения в бандлах по пути $path
    static function Build( $path , $force = false ) {} //Делает сборку приложения из бандлов найденных по заданному пути








    static function pt ()
    {return 23;}
}
