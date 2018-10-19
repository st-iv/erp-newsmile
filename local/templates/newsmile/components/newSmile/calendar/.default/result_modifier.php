<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile\Config;
use Mmit\NewSmile;

$arResult['JS_PARAMS'] = [
    'dateFrom' => $arResult['START_DAY'],
    'dateTo' => $arResult['END_DAY'],
];

foreach ($arResult['DATE'] as $strDate => $date)
{
    $arResult['JS_PARAMS']['dateData'][$strDate] = [
        'startTime' => Config::getScheduleStartTime(),
        'endTime' => Config::getScheduleEndTime(),
        'generalTime' => NewSmile\Date\Helper::formatTimeInterval($date['GENERAL_MINUTES'] * 60),
        'engagedTime' => NewSmile\Date\Helper::formatTimeInterval($date['ENGAGED_MINUTES'] * 60),
        'patientsCount' => $date['PATIENTS']
    ];
}