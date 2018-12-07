<?

namespace Mmit\NewSmile\Command\Doctor;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\DoctorSpecializationTable;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Scheduler;

class GetList extends Base 
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
            'offset' => [
                'TITLE' => 'смещение выборки от начала',
                'REQUIRED' => false
            ],
            'limit' => [
                'TITLE' => 'ограничение количества',
                'REQUIRED' => false
            ],
            'sort_by' => [
                'TITLE' => 'поле для сортировки',
                'REQUIRED' => false
            ],
            'sort_order' => [
                'TITLE' => 'направление сортировки',
                'REQUIRED' => false
            ],
            'select' => [
                'TITLE' => 'поля для выборки',
                'REQUIRED' => false,
                'DEFAULT' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'ID'],
                'OPERATION' => 'read-full-info'
            ],
            'get-schedule' => [
                'TITLE' => 'флаг запроса расписания',
                'DEFAULT' => false
            ],
            'get-specialization' => [
                'TITLE' => 'флаг запроса специальности',
                'DEFAULT' => false
            ]
        ];
    }
}