<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Mmit\NewSmile\Helpers;

foreach ($arResult['DOCTORS'] as &$doctor)
{
    $doctor = Helpers::getFio($doctor);
}

unset($doctor);

foreach ($arResult['WORK_CHAIR'] as &$workChair)
{
    foreach ($workChair['SCHEDULES'] as &$schedule)
    {
        $schedule['UF_DOCTOR_NAME'] = Helpers::getFio($schedule, 'UF_DOCTOR_');
        $schedule['UF_MAIN_DOCTOR_NAME'] = Helpers::getFio($schedule, 'UF_MAIN_DOCTOR_');
    }

    unset($schedule);
}

unset($workChair);
