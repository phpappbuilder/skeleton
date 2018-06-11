<?php
namespace Core;

use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;



class Builder
{

    private $temp = 'var/space';
    private $BundleList = [];
    private $KeyList = [];
    private $CollectionList = [];

    /* Keys */
    //возвращает спсиок ключей в пространстве
    public function GetKeys( $path ) {
        
    }

    //Возвращает все возможные значения ключа
    public function GetValues( $path ) {
        $space = explode ("/",$path);
        if (is_file($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/variations.php')) {
            $file = require ($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/variations.php');
            for ($i=0;$i<count($file);$i++)
                {
                    $result[$i]=$file[$i]['name'];
                }
            return $result;
        }
        return null;
    }

    //присваивает ключу значение по id из GetValues
    public function SelectValue( $path , $id ) {
        $space = explode ("/",$path);
        if (is_file($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/variations.php')) {
            $file = $this->TreeView($this->ParseCode(file_get_contents($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/variations.php')));

            if (isset($file[0]['expr']['items'][$id])) {
                foreach ($file[0]['expr']['items'] as $key => $value) {
                    $trans = $value['value']['items'];
                    $trans_c = count($trans);
                    for ($i = 0; $i < $trans_c; $i++) {
                        if ($trans[$i]['key']['value'] == 'checked') {
                            unset($file[0]['expr']['items'][$key]['value']['items'][$i]);
                            array_values($file[0]['expr']['items'][$key]['value']['items']);
                            break;
                        }
                    }
                }

                $file[0]['expr']['items'][$id]['value']['items'][] = array (
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
                file_put_contents($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/variations.php', $this->BuildCode($this->AstView($file)));
                for ($i=0;$i<count($file[0]['expr']['items'][$id]['value']['items']);$i++)
                    {
                        if ($file[0]['expr']['items'][$id]['value']['items'][$i]['key']['value']=='value')
                            {
                                $cont = $file[0]['expr']['items'][$id]['value']['items'][$i]['value'];
                                if (is_file($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/value.php')) {
                                    $return = $this->TreeView($this->ParseCode(file_get_contents($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/value.php')));
                                    $return[0]['expr'] = $cont;
                                    file_put_contents($this->temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/value.php', $this->BuildCode($this->AstView($return)));
                                    return true;
                                }
                            }
                    }

            }
            return false;



        }
        return false;
    }


    /* Collections */
    //Возвращает список всех коллекций в пространстве
    static function GetCollections( $path ) {}

    // Возврщает коллекцию с названиями и id
    static function ListCollection( $path ) {}

    //Делает видимым или невидимым эллемент коллекции по id
    static function CollectionItemStatus( $path , $id , $enabled = true ) {}


    /* Build */
    //Рекурсивно бегает по папкам и сохраняет все найденные бандлы в $this->BundleList
    private function Iterator( $dir ) {
      $files = [];

      if ($handle = opendir($dir))
        {
          while (false !== ($item = readdir($handle)))
            {
              if (is_file($dir.'/'.$item))
                {

                  if ($item == 'SpaceBundle.php')
                    {
                      $files[] = $dir.'/'.$item;
                      if (!in_array($dir.'/'.$item, $this -> BundleList)) {$this -> BundleList [] = $dir.'/'.$item;}
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

                            if (!in_array($bundle[$i]['Space'], $this -> BundleList))
                                {$this -> BundleList [] = $bundle[$i]['Space'];}
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
            $this->isKey($vendor,$app,$key);
            $buffer = $this->TreeView($this->ParseCode(file_get_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key.'/variations.php')));
            $buffer[0]['expr']['items'][] = $code;
            file_put_contents($this->temp.'/'.$vendor.'/'.$app.'/'.'key'.'/'.$key.'/variations.php', $this->BuildCode($this->AstView($buffer)));
        }

    public function test( $file )
    {
        return json_encode($this->TreeView($this->ParseCode(file_get_contents('var/space/root/core/key/attr/value.php'))));
    }

    //сбрасывает кеш
    private function FlushCache ()
        {
            $this->BundleList=[];
            $this->CollectionList=[];
            $this->KeyList=[];
        }

    //Делает сборку приложения из бандлов найденных по заданному пути
    public function Build( $path ) {
        $this->FlushCache();
        $this->Iterator( $path );
        $this->BundleParser();
        print_r([$this->BundleList,$this->CollectionList,$this->KeyList]);
            //collection
            $count = count($this->CollectionList);
            for ($i=0;$i<$count;$i++)
                {
                    $position = $this->CollectionList[$i];
                    $this->AddToCollection($position['vendor'],$position['app'],$position['collection'], $this ->PositionParserCollection($position['file'], $position['position'], true , true));
                }

            //key
            $count = count($this->KeyList);
            for ($i=0;$i<$count;$i++)
                {
                    $position = $this->KeyList[$i];
                    $this->AddToKey($position['vendor'],$position['app'],$position['key'], $this ->PositionParserKey($position['file'], $position['position'], true));
                }
    }

    //удаляет из пространств все найденные значения в бандлах по пути $path
    private function DeletePath( $path ) {
        $this->FlushCache();
        $this->Iterator( $path );
        $this->BundleParser();
        print_r([$this->BundleList,$this->CollectionList,$this->KeyList]);
        //collection
        $count = count($this->CollectionList);
        for ($i=0;$i<$count;$i++)
        {
            $position = $this->CollectionList[$i];
            $this->AddToCollection($position['vendor'],$position['app'],$position['collection'], $this ->PositionParserCollection($position['file'], $position['position'], true , true));
        }

        //key
        $count = count($this->KeyList);
        for ($i=0;$i<$count;$i++)
        {
            $position = $this->KeyList[$i];
            $this->AddToKey($position['vendor'],$position['app'],$position['key'], $this ->PositionParserKey($position['file'], $position['position'], false));
        }
    }


    static function pt ()
    {return 23;}
}