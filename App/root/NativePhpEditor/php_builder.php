<?php
$_temp_var_ = 0 ; #Хранит идентификатор уникальной переменной
$_modules_arr = [] ; #Хранит список уникальных модулей которые использовались в данном сниппете
$_build_log = [] ;

function temp_var () #Создаёт уникальную переменную
  global $_temp_var_;
  $_temp_var_++;
  return '$_temp_var_'.$_temp_var_;
}

function template_data ($tpl  , $obj )
{

  $variable = temp_var();


  #input
    if (isset($obj['input'])
      {
        foreach ($obj['input'] as $key => $value)
        {
          $tak = pre_build($value);
          $repl='{!input.'.$key.'!}';
          $tpl = str_replace($repl, $tak['return'] , $tpl);
          $ret['pre'] = $ret['pre'] . $tak['pre'] ;
          $ret['begin'] = $ret['begin'] . $tak['begin'] ;
          $ret['require_once'] = $ret['require_once'] . $tak['require_once'] ;
        }
      }

      #inspector
        if (isset($obj['inspector'])
          {
            foreach ($obj['inspector'] as $key => $value)
            {
              $repl='{!inspector.'.$key.'!}';
              $tpl = str_replace($repl, $value , $tpl);
            }
          }

          if (isset($obj['callback'])
            {
              foreach ($obj['callback'] as $key => $value)
              {
                $repl='{!callback.'.$key.'!}';
                $tpl = str_replace($repl, Build ($value) , $tpl);
              }
            }

            if (file_exist($obj['route'].'/return.php'))
              {
                $repl='{!return.variable!}';
                $tpl = str_replace($repl, template_data($obj['route'].'/return.php' , $obj) , $tpl);
              }
            else
            {
              $repl='{!return.variable!}';
              $tpl = str_replace($repl, $variable , $tpl);
            }

	return $run;
}


function pre_build ($obj) {
  if (file_exist($obj['route'].'/module.json'))
    {
      $info = json_decode(file_get_contents($obj['route'].'/module.json'));
      $variable = temp_var();
      if (file_exist($obj['route'].'/return.php'))
        {
          $ret['return'] = template_data($obj['route'].'/return.php' , $obj);
        }
      else
      {
        $ret['return'] = $variable;
      }


      #input
        if (isset($obj['input'])
          {
            foreach ($obj['input'] as $key => $value)
            {

            }
          }
      #inspector
      #return.variable
      #callback
    }
  else {
    $_build_log[] = "err : module_undefined";
    return "err(*";
  }

} #Делаем предворительное построение
  # строим код
  # в случае если return вставляем содержимое, если нет вставляем уникальную переменную
  # в случае если есть, то вставляем глобальные зависимости
  # в случае если есть вставляем глобальный код и инклуды в начало документа



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
blade ("share_1");
blade
    return $ret;



function build ($file) //билдим json древо в php код
{

  foreach ($file as $key => $value)
  {
    if (!in_array($key , $black_list))
    {
      $ret[$key] = pre_build($key , $file);

    }
  }
  return $ret;
}

?>
