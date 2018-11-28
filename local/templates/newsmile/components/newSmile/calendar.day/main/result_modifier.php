<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile;

$schedule = [];
$doctors = [];


foreach ($arResult['WORK_CHAIR'] as $workChairId => $workChair)
{
    $schedule[] = [
        'chair' => [
            'id' => $workChair['ID'],
            'name' => $workChair['NAME']
        ],
        'intervals' => $workChair['DOCTORS_SCHEDULE'],
        'visits' => array_map(function($visit)
        {
            $visit['TIME_START'] = $visit['TIME_START']->format('H:i');
            $visit['TIME_END'] = $visit['TIME_END']->format('H:i');
            return $visit;

        }, $workChair['VISITS'])
    ];
}

$doctors = array_map(function($doctor)
{
    return [
        'id' => $doctor['ID'],
        'fio' => \Mmit\NewSmile\Helpers::getFio($doctor),
        'color' => $doctor['COLOR']
    ];
}, $arResult['DOCTORS']);

$patients = array_map(function($patient)
{
    return [
        'name' => $patient['NAME'],
        'lastName' => $patient['LAST_NAME'],
        'secondName' => $patient['SECOND_NAME'],
        'age' => NewSmile\Date\Helper::getAge($patient['PERSONAL_BIRTHDAY']),
        'phone' => $patient['PERSONAL_PHONE'],
        'cardNumber' => $patient['NUMBER'],
        'statuses' => []
    ];
}, $arResult['PATIENTS']);

$curDate = new \DateTime($arResult['THIS_DATE']);

$arResult['JS_PARAMS'] = [
    'timeLimits' => [
        'start' => $arResult['SCHEDULE_START_TIME'],
        'end' => $arResult['SCHEDULE_END_TIME'],
    ],

    'curDate' => $arResult['THIS_DATE'],
    'curDateTitle' => NewSmile\Date\Helper::date('l_ru - d F_ru_gen', $curDate->getTimestamp()),
    'startTime' => $arResult['START_TIME'],
    'endTime' => $arResult['END_TIME'],

    'schedule' => $schedule,
    'doctors' => $doctors,
    'patients' => $patients,
];
