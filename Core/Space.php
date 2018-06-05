<?php
namespace Core;

use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;



class Space
{

    private $temp = 'var/space';
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

    //возвращает ассоциативный массив из AST
    private function TreeView ($code)
        {
            return json_decode(json_encode($code, JSON_PRETTY_PRINT), true);
        }

    //Делает из ассоциативного массива AST код
    private function AstView ($code)
        {
            $node = new \PhpParser\JsonDecoder;
            return $node->decode(json_encode($code));
        }

    //Генерирует PHP код из AST
    private function BuildCode($code)
        {
            $prettyPrinter = new PrettyPrinter\Standard;
            return $prettyPrinter->prettyPrintFile($code);
        }

    //Парсит PHP код в AST
    private function ParseCode($code){
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            return $parser->parse($code);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }
    }

    //Вытаскивает из бандала коллекцию находящийся в позиции N
    private function PositionParserCollection($file , $position, $collection = false , $enabled = true) {
        $ast = $this->ParseCode(file_get_contents($file));
        $str = $this ->TreeView($ast[0] -> expr -> items[$position]);
        $str['value']['items'][] = array (
            'nodeType' => 'Expr_ArrayItem',
            'key' =>
                array (
                    'nodeType' => 'Scalar_String',
                    'value' => 'bundle',
                    'attributes' =>
                        array (
                            'startLine' => 2,
                            'endLine' => 2,
                            'kind' => 2,
                        ),
                ),
            'value' =>
                array (
                    'nodeType' => 'Expr_Array',
                    'items' =>
                        array (
                            0 =>
                                array (
                                    'nodeType' => 'Expr_ArrayItem',
                                    'key' =>
                                        array (
                                            'nodeType' => 'Scalar_String',
                                            'value' => 'file',
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 2,
                                                ),
                                        ),
                                    'value' =>
                                        array (
                                            'nodeType' => 'Scalar_String',
                                            'value' => $file,
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 1,
                                                ),
                                        ),
                                    'byRef' => false,
                                    'attributes' =>
                                        array (
                                            'startLine' => 2,
                                            'endLine' => 2,
                                        ),
                                ),
                            1 =>
                                array (
                                    'nodeType' => 'Expr_ArrayItem',
                                    'key' =>
                                        array (
                                            'nodeType' => 'Scalar_String',
                                            'value' => 'position',
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 2,
                                                ),
                                        ),
                                    'value' =>
                                        array (
                                            'nodeType' => 'Scalar_LNumber',
                                            'value' => $position,
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 10,
                                                ),
                                        ),
                                    'byRef' => false,
                                    'attributes' =>
                                        array (
                                            'startLine' => 2,
                                            'endLine' => 2,
                                        ),
                                ),
                        ),
                    'attributes' =>
                        array (
                            'startLine' => 2,
                            'endLine' => 2,
                            'kind' => 2,
                        ),
                ),
            'byRef' => false,
            'attributes' =>
                array (
                    'startLine' => 2,
                    'endLine' => 2,
                ),
        );
        if ($collection)
        {
            if ($enabled) {$ttr = 'true';} else {$ttr = 'false';}
            $str['value']['items'][] = array (
                'nodeType' => 'Expr_ArrayItem',
                'key' =>
                    array (
                        'nodeType' => 'Scalar_String',
                        'value' => 'enabled',
                        'attributes' =>
                            array (
                                'startLine' => 2,
                                'endLine' => 2,
                                'kind' => 2,
                            ),
                    ),
                'value' =>
                    array (
                        'nodeType' => 'Expr_ConstFetch',
                        'name' =>
                            array (
                                'nodeType' => 'Name',
                                'parts' =>
                                    array (
                                        0 => $ttr,
                                    ),
                                'attributes' =>
                                    array (
                                        'startLine' => 2,
                                        'endLine' => 2,
                                    ),
                            ),
                        'attributes' =>
                            array (
                                'startLine' => 2,
                                'endLine' => 2,
                            ),
                    ),
                'byRef' => false,
                'attributes' =>
                    array (
                        'startLine' => 2,
                        'endLine' => 2,
                    ),
            );
        }
        return $str;
    }

    //Вытаскивает из бандала ключ находящийся в позиции N
    private function PositionParserKey($file , $position, $checked = false) {
        $ast = $this->ParseCode(file_get_contents($file));
        $str = $this ->TreeView($ast[0] -> expr -> items[$position]);
        $str['value']['items'][] = array (
            'nodeType' => 'Expr_ArrayItem',
            'key' =>
                array (
                    'nodeType' => 'Scalar_String',
                    'value' => 'bundle',
                    'attributes' =>
                        array (
                            'startLine' => 2,
                            'endLine' => 2,
                            'kind' => 2,
                        ),
                ),
            'value' =>
                array (
                    'nodeType' => 'Expr_Array',
                    'items' =>
                        array (
                            0 =>
                                array (
                                    'nodeType' => 'Expr_ArrayItem',
                                    'key' =>
                                        array (
                                            'nodeType' => 'Scalar_String',
                                            'value' => 'file',
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 2,
                                                ),
                                        ),
                                    'value' =>
                                        array (
                                            'nodeType' => 'Scalar_String',
                                            'value' => $file,
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 1,
                                                ),
                                        ),
                                    'byRef' => false,
                                    'attributes' =>
                                        array (
                                            'startLine' => 2,
                                            'endLine' => 2,
                                        ),
                                ),
                            1 =>
                                array (
                                    'nodeType' => 'Expr_ArrayItem',
                                    'key' =>
                                        array (
                                            'nodeType' => 'Scalar_String',
                                            'value' => 'position',
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 2,
                                                ),
                                        ),
                                    'value' =>
                                        array (
                                            'nodeType' => 'Scalar_LNumber',
                                            'value' => $position,
                                            'attributes' =>
                                                array (
                                                    'startLine' => 2,
                                                    'endLine' => 2,
                                                    'kind' => 10,
                                                ),
                                        ),
                                    'byRef' => false,
                                    'attributes' =>
                                        array (
                                            'startLine' => 2,
                                            'endLine' => 2,
                                        ),
                                ),
                        ),
                    'attributes' =>
                        array (
                            'startLine' => 2,
                            'endLine' => 2,
                            'kind' => 2,
                        ),
                ),
            'byRef' => false,
            'attributes' =>
                array (
                    'startLine' => 2,
                    'endLine' => 2,
                ),
        );
        if ($checked)
        {
            $str['value']['items'][] = array (
                'nodeType' => 'Expr_ArrayItem',
                'key' =>
                    array (
                        'nodeType' => 'Scalar_String',
                        'value' => 'checked',
                        'attributes' =>
                            array (
                                'startLine' => 2,
                                'endLine' => 2,
                                'kind' => 2,
                            ),
                    ),
                'value' =>
                    array (
                        'nodeType' => 'Expr_ConstFetch',
                        'name' =>
                            array (
                                'nodeType' => 'Name',
                                'parts' =>
                                    array (
                                        0 => 'true',
                                    ),
                                'attributes' =>
                                    array (
                                        'startLine' => 2,
                                        'endLine' => 2,
                                    ),
                            ),
                        'attributes' =>
                            array (
                                'startLine' => 2,
                                'endLine' => 2,
                            ),
                    ),
                'byRef' => false,
                'attributes' =>
                    array (
                        'startLine' => 2,
                        'endLine' => 2,
                    ),
            );
        }
        return $str;
    }

    //в случае отсуствия создаёт дирректории и файлы для коллекции
    public function isCollection ($vendor , $app , $collection)
        {
            if (!is_dir($this->temp.'/'.$vendor)) {mkdir($this->temp.'/'.$vendor, 0700);}
            if (!is_dir($this->temp.'/'.$vendor.'/'.$app)) {mkdir($this->temp.'/'.$vendor.'/'.$app, 0700);}
            if (!is_dir($this->temp.'/'.$vendor.'/'.$app.'/'.'collection')) {mkdir($this->temp.'/'.$vendor.'/'.$app.'/'.'collection', 0700);}
            if (!is_dir($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection)) {mkdir($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection, 0700);}
            if (!is_file($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection.'/'.'collection.php')) {
                $code = array (0 => array ('nodeType' => 'Stmt_Return', 'expr' => array ('nodeType' => 'Expr_Array', 'items' => array (), 'attributes' => array ('startLine' => 1, 'endLine' => 3, 'kind' => 2,),), 'attributes' => array ('startLine' => 1, 'endLine' => 3,),),);
                file_put_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection.'/'.'collection.php', $this->BuildCode($this->AstView($code)));
            }
            if (!is_file($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection.'/'.'return.php')) {
                $code = array (0 => array ('nodeType' => 'Stmt_Return', 'expr' => array ('nodeType' => 'Expr_Array', 'items' => array (), 'attributes' => array ('startLine' => 1, 'endLine' => 3, 'kind' => 2,),), 'attributes' => array ('startLine' => 1, 'endLine' => 3,),),);
                file_put_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection.'/'.'return.php', $this->BuildCode($this->AstView($code)));
            }
        }

    //в случае отсуствия создаёт дирректории и файлы для ключа
    public function isKey ($vendor , $app , $key)
        {
            if (!is_dir($this->temp.'/'.$vendor)) {mkdir($this->temp.'/'.$vendor, 0700);}
            if (!is_dir($this->temp.'/'.$vendor.'/'.$app)) {mkdir($this->temp.'/'.$vendor.'/'.$app, 0700);}
            if (!is_dir($this->temp.'/'.$vendor.'/'.$app.'/'.'key')) {mkdir($this->temp.'/'.$vendor.'/'.$app.'/'.'key', 0700);}
            if (!is_dir($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key)) {mkdir($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key, 0700);}
            if (!is_file($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key.'/'.'value.php')) {
                $code = array (0 => array ('nodeType' => 'Stmt_Return', 'expr' => NULL, 'attributes' => array ('startLine' => 1, 'endLine' => 1,),),);
                file_put_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key.'/'.'value.php', $this->BuildCode($this->AstView($code)));
            }
            if (!is_file($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key.'/'.'variations.php')) {
                $code = array (0 => array ('nodeType' => 'Stmt_Return', 'expr' => array ('nodeType' => 'Expr_Array', 'items' => array (), 'attributes' => array ('startLine' => 1, 'endLine' => 3, 'kind' => 2,),), 'attributes' => array ('startLine' => 1, 'endLine' => 3,),),);
                file_put_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key.'/'.'variations.php', $this->BuildCode($this->AstView($code)));
            }
        }

    //Добавить эллемент коллекции в пространство
    public function AddToCollection($vendor , $app , $collection , $code)
        {
            $this->isCollection($vendor,$app,$collection);
            $buffer = $this->TreeView($this->ParseCode(file_get_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection.'/collection.php')));
            $buffer[0]['expr']['items'][] = $code;
            file_put_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'collection'.'/'.$collection.'/collection.php', $this->BuildCode($this->AstView($buffer)));
        }

    //Добавить ключ в пространство
    public function AddToKey($vendor , $app , $key , $code)
        {

        }

    public function test( $file )
    {
        $this -> Iterator( $file );
        $this -> BundleParser();
        $this->AddToCollection('root','core','brb', $this ->PositionParserCollection('App/root/core/SpaceBundle.php', 0, true , true));
        return $this ->PositionParserCollection('App/root/core/SpaceBundle.php', 0, true , true);
    }
    private function DeletePath( $path ) {} //удаляет из пространств все найденные значения в бандлах по пути $path
    static function Build( $path , $force = false ) {} //Делает сборку приложения из бандлов найденных по заданному пути








    static function pt ()
    {return 23;}
}
