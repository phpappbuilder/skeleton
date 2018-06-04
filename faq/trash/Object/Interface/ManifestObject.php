<?
  /**
   *
   */
  interface Object
  {

    public function manifest () {} /* {"name":"" , "icon":"" , "info":"" , "extend":{"name::class", "name2::class", "..."},
                                      "heir":{"name::class", "name2::class"}}
                                      Баззовая информация о объекте*/

    public function getKeys () {} /* Возвращает ключи данного объекта c указанием хелпера и required */

    public function objectBundle () {} /* Возвращает имя класса объекта */

  }
