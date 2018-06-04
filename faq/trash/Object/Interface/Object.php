<?

/**
 *
 */
interface objectBundle
{

  public function __construct($id)  //конструктор объекта

  public function getKey ($key = '')  /* Возвращает значение ключа */

  public function updateKey ($key , $value)  /* Обновляет значение ключа , возвращает true в случае успеха, или false */

  public function getHeir ()  /* Возвращает объект от которого унаследован. В случае если данный объект самый старший, возвращает false*/

  public function listChild ($page)  /* Возвращает объекты которые наследуют данный объект */

  public function create ($extend , $values)
  public function read ()  /* Возвращает ключи и их значение */
  public function update ($values)
  public function delete ()

}
