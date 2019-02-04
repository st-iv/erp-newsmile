<?

namespace Mmit\NewSmile\Command\Visit;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command;
use Mmit\NewSmile\CommandVariable\ArrayParam;
use Mmit\NewSmile\CommandVariable\Date;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\CommandVariable\Time;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Orm;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\ScheduleTable;

class GetAddFormInfo extends Base
{
    public function getDescription()
    {
        return 'Возвращает информацию для формы добавления приёма';
    }

    protected function doExecute()
    {
        /* список врачей, которых можно назначить на приём, а также назначенного врача */

        $changeDoctorCommand = new Command\Schedule\ChangeDoctor($this->params, 'doctorId');
        $doctors = [];

        if($changeDoctorCommand->isAvailable())
        {
            $curDoctor = $this->getCurrentDoctor();
            $doctors[] = [
                'code' => $curDoctor['ID'],
                'name' => $curDoctor['FIO']
            ];

            $doctors = array_merge($doctors, $changeDoctorCommand->getVariants());

            $doctors = array_filter($doctors, function($doctor)
            {
                return ($doctor['code'] == true);
            });

            $doctorsIds = [];
            array_walk($doctors, function($doctor) use (&$doctorsIds)
            {
                $doctorsIds[] = $doctor['code'];
            });

            $colors = $this->getDoctorsColors($doctorsIds);

            $this->result['doctors'] = array_map(function($doctor) use ($colors, $curDoctor)
            {
                return [
                    'fio' => $doctor['name'],
                    'color' => $colors[$doctor['code']],
                    'isCurrent' => ($doctor['code'] == $curDoctor['ID']),
                    'code' => $doctor['code']
                ];
            }, array_values($doctors));
        }

        /* описание полей сущности пациента */

        $fieldsCodes = array_map(function($fieldCode)
        {
            return Helpers::getSnakeCase($fieldCode);
        }, $this->params['fields']);

        $this->result['fields'] = Orm\Helper::getFieldsDescription(PatientCardTable::class, $fieldsCodes);


        /* список пациентов */

        $patientsListCommand = new Command\PatientCard\GetList([
            'order' => [
                'lastName' => 'asc'
            ],
            'limit' => $this->params['patientsCount'],
            'countTotal' => true,
            'select' => $this->params['patientsSelect']
        ]);

        $patientsListCommand->execute();
        $patients = $patientsListCommand->getResult();
        $this->result['patients'] = $patients['list'];
        $this->result['patientsTotalCount'] = $patients['count'];
    }

    protected function getCurrentDoctor()
    {
        $startTime = new \DateTime($this->params['date'] . ' ' . $this->params['timeStart']);
        $endTime = new \DateTime($this->params['date'] . ' ' . $this->params['timeEnd']);

        $dbSchedules = ScheduleTable::getList([
            'filter' => [
                '>=TIME' => DateTime::createFromPhp($startTime),
                '<TIME' => DateTime::createFromPhp($endTime),
                'CLINIC_ID' => Config::getClinicId(),
                'WORK_CHAIR_ID' => $this->params['chairId']
            ],
            'select' => [
                'DOCTOR_ID',
                'WORK_CHAIR_ID',
                'DOCTOR_NAME' => 'DOCTOR.NAME',
                'DOCTOR_LAST_NAME' => 'DOCTOR.LAST_NAME',
                'DOCTOR_SECOND_NAME' => 'DOCTOR.SECOND_NAME',
            ]
        ]);

        $doctorId = 0;
        $doctorFio = '';

        while($schedule = $dbSchedules->fetch())
        {
            if(!$schedule['DOCTOR_ID'])
            {
                throw new Error('Не назначен врач на ' . $schedule['TIME']->format('Y-m-d H:i'), 'NOT_DEFINED_DOCTOR');
            }

            if($doctorId && ($schedule['DOCTOR_ID'] != $doctorId))
            {
                throw new Error('На выбранный интервал времени назначены несколько разных врачей', 'SEVERAL_DOCTORS_DEFINED');
            }
            elseif(!$doctorId)
            {
                $doctorId = $schedule['DOCTOR_ID'];
                $doctorFio = Helpers::getFio($schedule, 'DOCTOR_');
            }
        }

        return [
            'ID' => $doctorId,
            'FIO' => $doctorFio
        ];
    }

    public function getParamsMap()
    {
        return [
            new Time('timeStart', 'начальное время приема', true),
            new Time('timeEnd', 'конечное время приема', true),
            new Date('date', 'дата приема', true),
            new Integer('chairId', 'id кресла', true),
            new ArrayParam('fields', 'используемые поля', false, []
            ),
            new Integer('patientsCount', 'количество пациентов', false, 20
            ),
            Command\PatientCard\GetList::getParam('select')->setCode('patientsSelect')
        ];
    }

    protected static function getOperations()
    {
        return ['add'];
    }

    protected function getDoctorsColors($doctorsIds)
    {
        $result = [];

        $dbDoctors = DoctorTable::getList([
            'filter' => [
                'ID' => $doctorsIds
            ],
            'select' => ['ID', 'COLOR']
        ]);

        while($doctor = $dbDoctors->fetch())
        {
            $result[$doctor['ID']] = $doctor['COLOR'];
        }

        return $result;
    }
}