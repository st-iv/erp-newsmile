<?


namespace Mmit\NewSmile\Command\Visit;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam\Date;
use Mmit\NewSmile\CommandParam\Integer;
use Mmit\NewSmile\CommandParam\Time;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\ScheduleTable;
use Mmit\NewSmile\VisitTable;

class Add extends Base
{
    protected static $name = 'Записать пациента';
    protected $recordedPatients;

    protected function doExecute()
    {
        $timeStart = new DateTime($this->params['date'] . ' ' . $this->params['timeStart'], 'Y-m-d H:i');
        $timeEnd = new DateTime($this->params['date'] . ' ' . $this->params['timeEnd'], 'Y-m-d H:i');

        $addResult = VisitTable::add([
            'TIME_START' => $timeStart,
            'TIME_END' => $timeEnd,
            'PATIENT_ID' => $this->params['patientId'],
            'WORK_CHAIR_ID' => $this->params['chairId']
        ]);

        if(!$addResult->isSuccess())
        {
            throw new Error(implode(';', $addResult->getErrorMessages()), 'VISIT_ADD_ERROR');
        }
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
            //$this->variants = $this->getPatients($recordedPatients);
        }
        else
        {
            $isAllowed = $isAllowed && !in_array($this->params['patientId'], $recordedPatients);
        }

        return $isAllowed;
    }

    protected function getPatients(array $except)
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
            new Time('timeStart', 'Начало интервала', '', true),
            new Time('timeEnd', 'Конец интервала', '', true),
            new Integer('chairId', 'id кресла', '', true),
            new Date('date', 'дата', '', true),
            new Integer('patientId', 'id кресла', '', true),
        ];
    }

    protected static function getOperations()
    {
        return ['add'];
    }
}