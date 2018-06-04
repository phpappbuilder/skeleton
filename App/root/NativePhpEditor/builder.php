<?php
$file = json_decode(file_get_contents('project/site/run/controller/index/build.json') , true);

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

print_r(json_encode(build($file['modules'])) . "<br>");

print_r($black_list);


 ?>
