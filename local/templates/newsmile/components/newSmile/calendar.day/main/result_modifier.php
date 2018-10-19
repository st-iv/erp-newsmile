<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile;

$arResult['JS_PARAMS'] = array(
    'curDate' => $arResult['THIS_DATE'],
    'curTime' => date('H:i'),
    'startTime' => $arResult['START_TIME'],
    'endTime' => $arResult['END_TIME'],
);

$doctors = [];
$patients = [];

foreach ($arResult['WORK_CHAIR'] as $workChairId => $workChair)
{
    $prevDoctorId = 0;

    /**
     * @var \Bitrix\Main\Type\DateTime $intervalStartTime
     */
    $intervalStartTime = null;
    $counter = 0;
    $schedulesCount = count($workChair['SCHEDULES']);

    /* подготовка doctors (заполнение массива workTime) */

    foreach ($workChair['SCHEDULES'] as $schedule)
    {
        $counter++;
        $isLastItem = ($counter == $schedulesCount);

        if($isLastItem || ($schedule['UF_DOCTOR_ID'] != $prevDoctorId))
        {
            if($prevDoctorId)
            {
                if($isLastItem)
                {
                    $endDateTime = new \DateTime();
                    $endDateTime->setTimestamp($schedule['TIME']->getTimestamp());
                    $endDateTime->modify('+' . $schedule['DURATION'] . ' minute');

                    $endTime = $endDateTime->format('H:i');
                }
                else
                {
                    $endTime = $schedule['TIME']->format('H:i');
                }

                // записываем интервал
                $doctors[$prevDoctorId]['workTime'][] = [
                    'startTime' => $intervalStartTime->format('H:i'),
                    'endTime' => $endTime,
                    'roomId' => $workChairId
                ];
            }

            $intervalStartTime = $schedule['TIME'];
        }

        $prevDoctorId = $schedule['UF_DOCTOR_ID'];
    }

    /* заполнение mainDoctors */

    if($workChair['MAIN_DOCTORS'][0])
    {
        $arResult['JS_PARAMS']['mainDoctors'][] = [
            'roomId' => $workChairId,
            'doctorId' => $workChair['MAIN_DOCTORS'][0],
            'halfDayNum' => 1
        ];
    }

    if($workChair['MAIN_DOCTORS'][1])
    {
        $arResult['JS_PARAMS']['mainDoctors'][] = [
            'roomId' => $workChairId,
            'doctorId' => $workChair['MAIN_DOCTORS'][1],
            'halfDayNum' => 2
        ];
    }

    /* заполнение rooms */
    $arResult['JS_PARAMS']['rooms'][] = [
        'id' => $workChairId,
        'name' => $workChair['NAME']
    ];

    /* подготовока patients (заполнение массива visits) */

    foreach ($workChair['VISITS'] as $visit)
    {
        $patients[$visit['UF_PATIENT_ID']]['visits'][] = [
            'timeFrom' => $visit['TIME_START']->format('H:i'),
            'timeTo' => $visit['TIME_END']->format('H:i'),
            'roomId' => $workChairId,
            'doctorId' => $visit['UF_DOCTOR_ID']
        ];
    }
}

/* заполнение doctors */

foreach ($doctors as $doctorId => &$interval)
{
    $interval['id'] = $doctorId;
    $interval['name'] = NewSmile\Helpers::getFio($arResult['DOCTORS'][$doctorId]);
    $interval['color'] = substr($arResult['DOCTORS'][$doctorId]['COLOR'], 1);
}

unset($interval);

$arResult['JS_PARAMS']['doctors'] = array_values($doctors);

/* заполнение patients */

foreach ($patients as $patientId => &$patient)
{
    $patientInfo =& $arResult['PATIENTS'][$patientId];

    $patient['id'] = $arResult['PATIENTS'][$patientId]['NUMBER'];
    $patient['info']['fullName'] = $patientInfo['LAST_NAME'] . ' ' . $patientInfo['NAME'] . ' ' . $patientInfo['SECOND_NAME'];
    $patient['info']['age'] = NewSmile\Date\Helper::getAge($patientInfo['PERSONAL_BIRTHDAY'], true);
    $patient['info']['phone'] = $patientInfo['PERSONAL_PHONE'];
}

unset($patient);
unset($patientInfo);

$arResult['JS_PARAMS']['patients'] = array_values($patients);

pr($arResult['JS_PARAMS']);

