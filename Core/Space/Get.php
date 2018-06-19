<?php

namespace Core\Space;

class Get
{

    public static $temp = 'var/space';

    //Возвращает значение ключа
    static function Key( $path ) {
        $space = explode ("/",$path);
        if (is_file(self::$temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/value.php')) {
            return require (self::$temp.'/'.$space['0'].'/'.$space['1'].'/key/'.$space['2'].'/value.php');
        }
        return null;
    }

    //возвращает коллекцию
    static function Collection( $path ) {
        $space = explode ("/",$path);
        if (is_file(self::$temp.'/'.$space['0'].'/'.$space['1'].'/collection/'.$space['2'].'/return.php')) {
            return require (self::$temp.'/'.$space['0'].'/'.$space['1'].'/collection/'.$space['2'].'/return.php');
        }
        return null;
    }

}