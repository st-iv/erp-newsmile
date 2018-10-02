<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['DOCTORS'] as &$doctor)
{
    $doctor['COLOR'] = str_replace('#', '', $doctor['COLOR']);
}

unset($doctor);