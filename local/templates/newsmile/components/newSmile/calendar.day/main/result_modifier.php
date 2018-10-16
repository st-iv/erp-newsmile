<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult['JS_PARAMS'] = array(
    'curDate' => $arResult['THIS_DATE'],
    'curTime' => date('H:i'),
    'startTime' => $arResult['START_TIME'],
    'endTime' => $arResult['END_TIME'],
);

foreach ($arResult['WORK_CHAIR'] as $workChair)
{
    /*foreach ($workChair['SCHEDULES'] as $schedule)
    {
        pr($schedule);
    }*/

    /*pr($workChair);*/


}

pr($arResult);

