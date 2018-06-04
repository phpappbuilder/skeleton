<?php

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
$str = '{"m2":{"route":"logic\/ifelse","order":"32","input":{"condition":{"route":"check\/isjson","input":{"value":{"route":"variables\/varval","inspector":{"name":"_GET"}}}}},"callback":{"true":{"m3":{"route":"variables\/varval--","inspector":{"name":"i"},"layer":0}},"false":{"m4":{"route":"variables\/varval++","inspector":{"name":"i"},"layer":1}}}}}';
print_r(json_encode(code(json_decode($str , true))));
print_r($build_black_list);

?>
