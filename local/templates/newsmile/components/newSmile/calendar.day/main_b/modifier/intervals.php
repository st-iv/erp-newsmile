<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Mmit\NewSmile\Helpers;

foreach ($arResult['WORK_CHAIR'] as &$workChair)
{
    reset($arResult['TIME_LINE']);
    while($currentTimeItem = current($arResult['TIME_LINE']))
    {
        $currentTime = $currentTimeItem['TIME'];
        $currentTimeIndex = key($arResult['TIME_LINE']);
        $strCurTime = $currentTime->format('H:i');

        if($workChair['VISITS'][$strCurTime] && $workChair['VISITS'][$strCurTime]['ID'])
        {
            $interval = $workChair['VISITS'][$strCurTime];
            $interval['IS_VISIT'] = true;
            $interval['ROWS_COUNT'] = 0;
            $interval['UF_PATIENT_FIO'] = Helpers::getFio($interval, 'UF_PATIENT_');
            $interval['UF_PATIENT_AGE'] = \Mmit\NewSmile\Date\Helper::getAge($interval['UF_PATIENT_PERSONAL_BIRTHDAY']);

            do
            {
                $currentTimeItem = next($arResult['TIME_LINE']);
                $currentTime = $currentTimeItem['TIME'];
                $interval['ROWS_COUNT']++;
            }
            while($currentTime->getTimestamp() < $interval['TIME_END']->getTimestamp());

            if(($interval['ROWS_COUNT'] == 1) && $interval['UF_DOCTOR_ID'])
            {
                $arResult['TIME_LINE'][$currentTimeIndex]['ROWS_COUNT'] = 2;
            }
        }
        else
        {
            $interval = $workChair['SCHEDULES'][$strCurTime];
            $interval['ROWS_COUNT'] = 1;
            next($arResult['TIME_LINE']);
        }

        $interval['UF_DOCTOR_FIO'] = Helpers::getFio($interval, 'UF_DOCTOR_');

        $workChair['INTERVALS'][$strCurTime] = $interval;
    }

    foreach ($workChair['MAIN_DOCTORS'] as &$mainDoctor)
    {
        $mainDoctor['FIO'] = Helpers::getFio($mainDoctor);
    }

    unset($mainDoctor);
}

/* добавляем количество строк у элементов TIME_LINE к соответствующим интервалам */
foreach ($arResult['TIME_LINE'] as $timeItem)
{
    foreach ($arResult['WORK_CHAIR'] as &$workChair)
    {
        if($timeItem['ROWS_COUNT'] > 1)
        {
            $workChair['INTERVALS'][$timeItem['TIME']->format('H:i')]['ROWS_COUNT'] += $timeItem['ROWS_COUNT'] - 1;
        }
    }

    unset($workChair);
}