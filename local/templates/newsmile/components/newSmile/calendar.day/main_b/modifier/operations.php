<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['WORK_CHAIR'] as &$workChair)
{
    foreach ($workChair['INTERVALS'] as &$interval)
    {
        $interval['OPERATIONS'] = array();
        $isEmpty = !($interval['UF_DOCTOR_ID'] || $interval['UF_MAIN_DOCTOR_ID']);

        /*--------------- CHANGE DOCTOR ---------------*/
        $doctorsToChange = array();

        foreach ($arResult['DOCTORS'] as $doctor)
        {
            if($isEmpty || $doctor['ID'] != ($interval['UF_DOCTOR_ID'] ?: $interval['UF_MAIN_DOCTOR_ID']))
            {
                $doctorsToChange[$doctor['ID']] = $doctor['FIO'];
            }
        }

        $changeDoctorTitle = $isEmpty ? 'Назначить' : 'Изменить';
        $changeDoctorTitle .= ' врача';


        $interval['OPERATIONS']['ChangeDoctor'] = array(
            'TITLE' => $changeDoctorTitle,
            'VARIANTS' => $doctorsToChange
        );

        if($interval['IS_VISIT'])
        {
            /*--------------- CANCEL VISIT ---------------*/

            $interval['OPERATIONS']['CancelVisit'] = array(
                'TITLE' => 'Отменить прием'
            );
        }
        else
        {
            /*--------------- SET PATIENT ---------------*/

            $patientsToSet = [];

            foreach($arResult['PATIENTS'] as $patient)
            {
                $patientsToSet[$patient['ID']] = $patient['FIO'];
            }

            $interval['OPERATIONS']['SetPatient'] = array(
                'TITLE' => 'Записать пациента',
                'VARIANTS' => $patientsToSet
            );
        }
    }

    unset($interval);
}

unset($workChair);