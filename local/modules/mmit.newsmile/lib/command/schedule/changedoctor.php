<?


namespace Mmit\NewSmile\Command\Schedule;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam\Date;
use Mmit\NewSmile\CommandParam\Integer;
use Mmit\NewSmile\CommandParam\Time;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Scheduler;
use Mmit\NewSmile\ScheduleTable;

class ChangeDoctor extends Base
{
    protected static $name = 'Изменить врача';

    protected function doExecute()
    {
        $date = new \DateTime($this->params['date']);
        $scheduler = new Scheduler($date);
        $scheduler->updateByTime($this->params['timeStart'], $this->params['timeEnd'], $this->params['chairId'], [
            'DOCTOR_ID' => $this->params['doctorId']
        ]);
        $scheduler->save();
    }

    protected function checkAvailable()
    {
        if($this->varyParam && ($this->varyParam !== 'doctorId'))
        {
            return false;
        }
        else
        {
            $bVaryDoctor = true;
        }

        $startTime = new \DateTime($this->params['date'] . ' ' . $this->params['timeStart']);
        $endTime = new \DateTime($this->params['date'] . ' ' . $this->params['timeEnd']);

        // проверяем по времени - врача можно изменить только для ещё не прошедшего интервала
        if(!$endTime->diff(new \DateTime())->invert)
        {
            return false;
        }

        /* запрашиваем все интервалы с указанным временем, чтобы вычислить, какие врачи заняты */
        $isAvailable = true;

        $dbSchedules = ScheduleTable::getList([
            'filter' => [
                '>=TIME' => DateTime::createFromPhp($startTime),
                '<TIME' => DateTime::createFromPhp($endTime),
                'CLINIC_ID' => Config::getClinicId()
            ],
            'select' => ['DOCTOR_ID', 'WORK_CHAIR_ID']
        ]);

        $busyDoctors = [];
        $currentDoctorId = null;

        while($schedule = $dbSchedules->fetch())
        {
            $busyDoctors[] = $schedule['DOCTOR_ID'];

            if($schedule['WORK_CHAIR_ID'] == $this->params['chairId'])
            {
                $currentDoctorId = $schedule['DOCTOR_ID'];
            }
        }

        if($bVaryDoctor)
        {
            if($isAvailable)
            {
                $this->variants = $this->getDoctorsList($busyDoctors);
                if($currentDoctorId)
                {
                    $this->variants[0] = 'не назначен';
                }

                ksort($this->variants);
            }
        }
        else
        {
            // нельзя назначить уже занятого врача
            $isAvailable = $isAvailable && !in_array($this->params['doctorId'], $busyDoctors);
        }

        return $isAvailable;
    }

    public function getParamsMap()
    {
        return [
            new Time('timeStart', 'Начало интервала', '', true),
            new Time('timeEnd', 'Конец интервала', '', true),
            new Integer('chairId', 'id кресла', '', true),
            new Date('date', 'дата', '', true),
            new Integer('doctorId', 'id врача', '', true),
        ];
    }

    protected static function getOperations()
    {
        return ['change-doctor'];
    }

    protected function getDoctorsList(array $except)
    {
        $result = [];

        $dbDoctors = DoctorTable::getList([
            'filter' => [
                '!ID' => $except
            ],
            'select' => ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME']
        ]);

        while($doctor = $dbDoctors->fetch())
        {
            $result[$doctor['ID']] = Helpers::getFio($doctor);
        }

        return $result;
    }
}