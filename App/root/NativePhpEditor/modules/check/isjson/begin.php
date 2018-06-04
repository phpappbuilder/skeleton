
function is_json ($value = null){
  $ret = true;

  if (null === @json_decode($value)){
      $ret = false;
  }

  return $ret;

}
