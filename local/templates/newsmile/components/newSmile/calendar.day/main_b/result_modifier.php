<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Mmit\NewSmile\Helpers;

foreach ($arResult['DOCTORS'] as &$doctor)
{
    $doctor['FIO'] = Helpers::getFio($doctor);
}

unset($doctor);

foreach ($arResult['PATIENTS'] as &$patient)
{
    $patient['FIO'] = Helpers::getFio($patient);
}

unset($patient);

foreach ($arResult['TIME_LINE'] as &$timeLine)
{
    $timeLine = array(
        'TIME' => $timeLine,
        'ROWS_COUNT' => 1,
    );
}

unset($timeLine);

include 'modifier/intervals.php';
include 'modifier/operations.php';
