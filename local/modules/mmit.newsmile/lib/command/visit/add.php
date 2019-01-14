<?


namespace Mmit\NewSmile\Command\Visit;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\Field;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Command\OrmEntityAdd;
use Mmit\NewSmile\Command\TeethMap\Detail;
use Mmit\NewSmile\Notice\NewVisitAdded;
use Mmit\NewSmile\ScheduleTable;
use Mmit\NewSmile\Visit\VisitTable;

class Add extends OrmEntityAdd
{
    protected static $name = 'Записать пациента';
    protected $recordedPatients;

    protected function getOrmEntity()
    {
        return VisitTable::getEntity();
    }

    protected function filterField(Field $field)
    {
        return in_array($field->getName(), ['TIME_START', 'TIME_END', 'PATIENT_ID', 'DOCTOR_ID', 'WORK_CHAIR_ID']);
    }

    protected function doExecute()
    {
        parent::doExecute();

        $notice = new NewVisitAdded([
            'VISIT_ID' => $this->result['primary']['id']
        ]);

        $notice->push([$this->params['patientId']]);
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

        $startTime = new \DateTime($this->params['timeStart']);
        $endTime = new \DateTime($this->params['timeEnd']);

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
            if($schedule['WORK_CHAIR_ID'] == $this->params['workChairId'])
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

        if(!$bVaryPatient)
        {
            $isAllowed = $isAllowed && !in_array($this->params['patientId'], $recordedPatients);
        }

        return $isAllowed;
    }


    protected static function getOperations()
    {
        return ['add'];
    }
}