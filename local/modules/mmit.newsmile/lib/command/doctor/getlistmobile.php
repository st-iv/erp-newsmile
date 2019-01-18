<?

namespace Mmit\NewSmile\Command\Doctor;

use Bitrix\Main\Entity\IntegerField;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam\ArrayParam;
use Mmit\NewSmile\CommandParam\Bool;
use Mmit\NewSmile\CommandParam\Integer;
use Mmit\NewSmile\CommandParam\String;
use Mmit\NewSmile\DoctorSpecializationTable;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Scheduler;

class GetListMobile extends Base
{
    protected static $name = 'Получить список врачей';

    protected function doExecute()
    {
        $queryParams = [
            'select' => $this->params['select'],
            'count_total' => true
        ];

        if($this->params['offset'])
        {
            $queryParams['offset'] = $this->params['offset'];
        }

        if($this->params['limit'])
        {
            $queryParams['limit'] = $this->params['limit'];
        }

        if($this->params['sort_by'] && $this->params['sort_order'])
        {
            $queryParams['order'] = [
                $this->params['sort_by'] => $this->params['sort_order']
            ];
        }

        /* запрос врачей */

        $dbDoctors = DoctorTable::getList($queryParams);
        $doctorsIds = [];
        $doctors = [];

        $this->result['total_count'] = $dbDoctors->getCount();

        while($doctor = $dbDoctors->fetch())
        {
            $doctorsIds[] = $doctor['ID'];
            $doctors[] = $doctor;
        }

        /* получение списка специальностей */

        $specializations = null;

        if($this->params['get-specialization'])
        {
            $specializations = $this->getSpecializations($doctorsIds);
        }

        /* получение расписания */

        $workSchedule = null;

        if($this->params['get-schedule'])
        {
            $workSchedule = Scheduler::getDoctorsSchedule();
        }

        /* объединение данных */

        foreach ($doctors as $doctor)
        {
            $doctor['fio'] = Helpers::getFio($doctor);
            $doctor = Helpers::strtolowerKeys($doctor);

            if(isset($specializations))
            {
                $doctor['specialization'] = DoctorSpecializationTable::getSpecName($specializations[$doctor['id']]);
                $doctor['specialization_code'] = $specializations[$doctor['id']];
            }

            if(isset($workSchedule))
            {
                $doctor['work_schedule'] = $workSchedule[$doctor['id']];
            }

            $this->result['list'][] = $doctor;
        }
    }

    protected function getSpecializations(array $doctorsIds)
    {
        $dbDoctorsSpecs = DoctorSpecializationTable::getList([
            'filter' => [
                'DOCTOR_ID' => $doctorsIds
            ]
        ]);

        $specializations = [];

        while($doctorSpec = $dbDoctorsSpecs->fetch())
        {
            $specializations[$doctorSpec['DOCTOR_ID']] = $doctorSpec['SPECIALIZATION'];
        }

        return $specializations;
    }

    public function getParamsMap()
    {
        return [
            new Integer('offset', 'смещение выборки от начала'),
            new Integer('limit', 'ограничение количества'),
            new String('sort_by', 'поле для сортировки'),
            new String('sort_order', 'направление сортировки'),
            (new ArrayParam(
                'select',
                'направление сортировки',
                '',
                false,
                ['NAME', 'LAST_NAME', 'SECOND_NAME', 'ID']
            ))->setOperations('read-full-info'),
            new Bool('get-schedule', 'флаг запроса расписания', '', false, false),
            new Bool('get-specialization', 'флаг запроса специальности', '', false, false),
        ];
    }
}