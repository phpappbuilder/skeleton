<?
namespace Centurion\Core\App;

class Render
{
  public function render($_tpl_,$v=array())
    {
      extract($v,EXTR_SKIP);
      ob_start();
      include($_tpl_);
      return ob_get_clean();
    }
}
