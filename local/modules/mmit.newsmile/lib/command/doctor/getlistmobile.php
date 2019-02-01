<?

namespace Mmit\NewSmile\Command\Doctor;

use Bitrix\Main\Entity\IntegerField;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\CommandVariable\ArrayParam;
use Mmit\NewSmile\CommandVariable\Bool;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\CommandVariable\Object;
use Mmit\NewSmile\CommandVariable\String;
use Mmit\NewSmile\DoctorSpecializationTable;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Scheduler;

class GetListMobile extends Base
{
    public function getDescription()
    {
        return 'Возвращает информацию о врачах в особом формате для мобильных приложений';
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
                'поля для выборки',
                false,
                ['NAME', 'LAST_NAME', 'SECOND_NAME', 'ID']
            ))->setContentType(
                new String('', '', true)
            )->setOperations('read-full-info'),
            new Bool('get-schedule', 'флаг запроса расписания', false, false),
            new Bool('get-specialization', 'флаг запроса специальности', false, false),
            new ArrayParam('ids', 'id врачей для выборки')
        ];
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            new Integer('total_count', 'общее количество записей', true),
            (new ArrayParam('list', 'список врачей, информация по каждому врачу зависит от параметра select', true))->setContentType(
                (new Object('', '', true))->setShape([
                    new String('specialization', 'название специализации врача, если был указан флаг get-specialization'),
                    new String('specialization_code', 'код специализации врача, если был указан флаг get-specialization'),
                    new Object('work_schedule', 'информация по расписанию, если был указан флаг get-schedule')
                ])->setFlexible(true)
            )
        ]);
    }

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

        if($this->params['ids'])
        {
            $queryParams['filter'] = [
                'ID' => $this->params['ids']
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
}