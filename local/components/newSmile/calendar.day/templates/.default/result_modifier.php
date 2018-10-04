<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Mmit\NewSmile\Helpers;

foreach ($arResult['DOCTORS'] as &$doctor)
{
    $doctor = Helpers::getFio($doctor['NAME'], $doctor['LAST_NAME'], $doctor['SECOND_NAME']);
}

unset($doctor);

foreach ($arResult['WORK_CHAIR'] as &$workChair)
{
    foreach ($workChair['SCHEDULES'] as &$schedule)
    {
        $schedule['UF_DOCTOR_NAME'] = Helpers::getFio($schedule['UF_DOCTOR_NAME'], $schedule['UF_DOCTOR_LAST_NAME'], $schedule['UF_DOCTOR_SECOND_NAME']);
        $schedule['UF_MAIN_DOCTOR_NAME'] = Helpers::getFio($schedule['UF_MAIN_DOCTOR_NAME'], $schedule['UF_MAIN_DOCTOR_LAST_NAME'], $schedule['UF_MAIN_DOCTOR_SECOND_NAME']);
    }

    unset($schedule);

    foreach ($workChair['MAIN_DOCTORS'] as &$mainDoctor)
    {
        if($mainDoctor['ID'])
        {
            $mainDoctor['NAME'] = Helpers::getFio($mainDoctor['NAME'], $mainDoctor['LAST_NAME'], $mainDoctor['SECOND_NAME']);
        }
    }

    unset($mainDoctor);
}

unset($workChair);
