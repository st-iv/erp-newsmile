<?


namespace Mmit\NewSmile\Command\Visit;

use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\ScheduleTable;

class Add extends Base
{
    protected static $name = 'Записать пациента';

    protected function doExecute()
    {
        // TODO: Implement doExecute() method.
    }

    protected function checkAvailable()
    {
        if($this->varyParam && ($this->varyParam !== 'patientId'))
        {
            return false;
        }
        else
        {
            $bVaryPatient = true;
        }

        $startTime = new \DateTime($this->params['date'] . ' ' . $this->params['timeStart']);
        $endTime = new \DateTime($this->params['date'] . ' ' . $this->params['timeEnd']);

        // проверяем по времени - пациента можно записать только на ещё не прошедшие интервалы расписания
        if(!$endTime->diff(new \DateTime())->invert)
        {
            return false;
        }

        $dbSchedules = ScheduleTable::getList([
            'filter' => [
                '>=TIME' => DateTime::createFromPhp($startTime),
                '<TIME' => DateTime::createFromPhp($endTime),
                'CLINIC_ID' => Config::getClinicId(),
            ],
            'select' => ['DOCTOR_ID', 'PATIENT_ID']
        ]);

        $isAllowed = true;
        $doctorId = null;
        $recordedPatients = [];

        while($schedule = $dbSchedules->fetch())
        {
            if($schedule['WORK_CHAIR_ID'] == $this->params['chairId'])
            {
                if($schedule['PATIENT_ID'] || !$schedule['DOCTOR_ID'] || (isset($doctorId) && $doctorId != $schedule['DOCTOR_ID']))
                {
                    // запись пациента не разрешается, если хоть на одном из интервалов уже записан пациент, не назначен врач, либо
                    // назначены разные врачи
                    $isAllowed = false;
                    break;
                }
                else
                {
                    $doctorId = $schedule['DOCTOR_ID'];
                }
            }
            else
            {
                $recordedPatients[] = $schedule['PATIENT_ID'];
            }
        }


        if($bVaryPatient)
        {
            $this->variants = $this->getPatients($recordedPatients);
        }
        else
        {
            $isAllowed = $isAllowed && !in_array($this->params['patientId'], $recordedPatients);
        }


        return $isAllowed;
    }

    protected function getPatients($except)
    {
        $dbPatients = PatientCardTable::getList([
            'filter' => [
                '!ID' => $except
            ],
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME']
        ]);

        $result = [];

        while($patient = $dbPatients->fetch())
        {
            $result[$patient['ID']] = Helpers::getFio($patient);
        }

        return $result;
    }

    public function getParamsMap()
    {
        return [
            'timeStart' => [
                'TITLE' => 'Начало интервала',
                'REQUIRED' => true
            ],
            'timeEnd' => [
                'TITLE' => 'Конец интервала',
                'REQUIRED' => true
            ],
            'chairId' => [
                'TITLE' => 'id кресла',
                'REQUIRED' => true
            ],
            'date' => [
                'TITLE' => 'дата',
                'REQUIRED' => true
            ],
            'patientId' => [
                'TITLE' => 'id пациента',
                'REQUIRED' => true
            ]
        ];
    }
}