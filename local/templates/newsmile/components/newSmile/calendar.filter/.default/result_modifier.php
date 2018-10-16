<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile\Helpers;

foreach ($arResult['DOCTORS'] as &$doctor)
{
    $doctor['COLOR'] = str_replace('#', '', $doctor['COLOR']);
    $doctor['FIO'] = Helpers::getFio($doctor);
}

unset($doctor);
