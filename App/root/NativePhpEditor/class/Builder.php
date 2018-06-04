<?
namespace Centurion\App\root\NativePhpEditor\class ;

class Builder
{
  private $doc ;
  private $pins = [];
  private $nodes = [];

  public function __construct ($doc)
  {
    $this -> doc = $doc;
    $this -> pins = count_connect_pins($doc['nodes']);
  }

  private function search_pins ($pin) //возвращает колличество подключений к ноде
    {
      $ret = 0;
      foreach ($this -> doc as $key => $value) {
        if (isset($value['input']))
          {
            $values = array_values($value['input']);
            if (in_array( $pin , $values )) {$ret++;}
          }
      }
      return $ret;
    }

  private function count_connect_pins ($doc) //возвращает массив с колличеством подключений к нодам
    {
      $ret = [];
      foreach ($doc as $key => $value) {
        $ret[$key] = $this -> search_pins($key);
      }
      return $ret;
    }















  function arr_search($arr , $layer='no') //ищем модули соответствующие определённому layout
  {
    $layer = strval($layer);
    if ($layer == 'no')
    {
      foreach ($arr as $key => $value) {
        if (!isset($arr[$key]['layer']))
          {
                $ret[$key] = $arr[$key];
          }
      }
    }
    else {
      foreach ($arr as $key => $value) {
      if ($arr[$key]['layer'] == $layer)
        {
          $ret[$key] = $arr[$key];
        }
      }
    }

      return $ret;
  }
  function arr_sort($arr) //сортируем от большего к меньшему по order
  {$i = 0;
    foreach ($arr as $key => $value)
    {
      if (isset($arr[$key]['order']))
        {
          $ar1[$i] = $arr[$key]['order'];
        }
        else
        {
          $ar1[$i] = 0;
        }
        $ar2[$i] = $key;
        $i++;
    }

    array_multisort($ar1, SORT_DESC , $ar2);

  for ($i=0;$i<count($ar2);$i++)
    {
      $ret[$ar2[$i]] = $arr[$ar2[$i]];
    }

  return $ret;
  }


  $black_list = []; //глобальный массив для хранения отработаных модулей (черного списка)
  $temp_file = '';
  $temp_cache = '';//временный кеш


  function pre_build ($key , $arr)
  {
    global $temp_file;
    global $black_list;
    $ret = $arr[$key];
    if (isset($arr[$key]['input']) and !empty($arr[$key]['input'])>0)
      {
        foreach ($arr[$key]['input'] as $keys => $values)
        {
            $ret['input'][$keys] = pre_build($values , $arr);
            $black_list[] = $key;
        }
      }
      else
      {
          $black_list[] = $key;
      }

      if (isset($arr[$key]['callback']) and count($arr[$key]['callback'])>0)
        {
          foreach ($arr[$key]['callback'] as $keys => $values)
          {
                $ret['callback'][$keys] = build($temp_file , $values);
          }

        }


      return $ret;
  }


  function build ($file , $layer = 'no') //билдим в json древо
  {
    $arr = arr_search ($file , $layer);
    $arr = arr_sort($arr);
    global $black_list;
    global $temp_file;
    if ($layer == 'no') {$temp_file = $file;}

    foreach ($arr as $key => $value)
    {
      if (!in_array($key , $black_list))
      {
        $ret[$key] = pre_build($key , $file);

      }
    }
    return $ret;
  }


  $_temp_var_ = 0 ; #Хранит идентификатор уникальной переменной
  $build_black_list = []; #Хранит уже учтённые модули

  function check_modules ($str)
  {
    global $build_black_list;
    if (in_array($str , $build_black_list))
      {
        return false;
      }
      else {
        $build_black_list [] = $str;
        return true;
      }
  }

  function temp_var () #Создаёт уникальную переменную
  {
    global $_temp_var_;
    $_temp_var_++;
    return '$_temp_var_'.$_temp_var_;
  }

  function templater ($str , $obj)
  {
    #inspector
      if (isset($obj['inspector']))
        {
          foreach ($obj['inspector'] as $key => $value)
          {
            $repl='{!inspector.'.$key.'!}';
            $str = str_replace($repl, $value , $str);
          }
        }

        #input
          if (isset($obj['input']))
            {
              foreach ($obj['input'] as $key => $value)
              {
                $repl='{!input.'.$key.'!}';
                $str = str_replace($repl, $value , $str);
              }
            }

            #callback
        if (isset($obj['callback']))
          {
            foreach ($obj['callback'] as $key => $value)
            {
              $repl='{!callback.'.$key.'!}';
              $str = str_replace($repl, $value , $str);
            }
          }


              $repl='{!return.variable!}';
              $str = str_replace($repl, $obj['variable'] , $str);

  $ret['return'] = preg_replace("~\{!.+?!\}~", '', $str);

  return $ret;
  }

  function code_builder ($obj)
  {
    $ret['require']='';
    $ret['begin']='';
    $ret['pre']='';
    if (isset($obj['input']))
      {
        foreach ($obj['input'] as $key => $value)
        {
          $kvo = code_builder ($value) ;
          $ret ['require'] = $kvo['require'] . $ret['require'];
          $ret ['begin'] = $kvo['begin'] . $ret['begin'];
          $ret ['pre'] = $kvo['pre'] . $ret['pre'];
          $input[$key] = $kvo['return'];
        }
      }

      if (isset($obj['callback']))
        {
          foreach ($obj['callback'] as $key => $value)
          {
            $call = code ($value);

            $ret['require'] = $call['require'] . $ret['require'];
            $ret['begin'] = $call['begin'] . $ret['begin'];

            $callback[$key] = $call['return'];

          }
        }
  $info = json_decode(file_get_contents('modules/'.$obj['route'].'/module.json') , true);
      $obj['input'] = $input;
      $obj['variable'] = temp_var();
      $obj['callback'] = $callback;



  if (check_modules($obj['route']))
  {
    if (is_file('modules/'.$obj['route'].'/require.php'))
    {
        $tpl = templater(file_get_contents('modules/'.$obj['route'].'/require.php') , $obj);
        $ret['require'] = $tpl['return'] . $ret['require'];
    }


    if (is_file('modules/'.$obj['route'].'/begin.php'))
    {
        $tpl = templater(file_get_contents('modules/'.$obj['route'].'/begin.php') , $obj);
        $ret['begin'] = $tpl['return'] . $ret['begin'];
    }
  }

  if (is_file('modules/'.$obj['route'].'/snippet.php'))
  {
      $tpl = templater(file_get_contents('modules/'.$obj['route'].'/snippet.php') , $obj);
      $ret['pre'] = $tpl['return'] . $ret['pre'];
  }


      if ($info['return'] == 'true')
      {
        if (is_file('modules/'.$obj['route'].'/return.php'))
          {
            $tpl = templater(file_get_contents('modules/'.$obj['route'].'/return.php') , $obj);
            $ret['return'] = $tpl['return'];
          }
        else
        {
            $ret['return'] = $obj['variable'];
        }
      }
      else
      {
        $ret['return'] = $ret['pre'];
      }
    return $ret;
  }

  function code ($obj)
  {
    foreach ($obj as $key => $value)
    {
        $tre = code_builder($obj[$key]);
        $ret['require'] = $tre['require'] . $ret['require'];
        $ret['begin'] = $tre['begin'] . $ret['begin'];
        $ret['return'] = $ret['return'] . $tre['pre'];
    }
      return $ret;
  }
}
