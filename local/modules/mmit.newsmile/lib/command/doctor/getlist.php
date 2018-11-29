<?

namespace Mmit\NewSmile\Command\Doctor;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\DoctorSpecializationTable;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Scheduler;

class GetList extends Base 
{
    public function execute()
    {
        $queryParams = [
            'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'ID']
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

        $dbDoctors = DoctorTable::getList($queryParams);
        $doctorsIds = [];
        $doctors = [];

        while($doctor = $dbDoctors->fetch())
        {
            $doctorsIds[] = $doctor['ID'];
            $doctors[] = $doctor;
        }

        $specializations = $this->getSpecializations($doctorsIds);
        $workSchedule = Scheduler::getDoctorsSchedule();

        foreach ($doctors as $doctor)
        {
            $doctorId = $doctor['ID'];
            unset($doctor['ID']);

            $doctor = Helpers::strtolowerKeys($doctor);
            $doctor['specialization'] = DoctorSpecializationTable::getSpecName($specializations[$doctorId]);
            $doctor['work_schedule'] = $workSchedule[$doctorId];

            $this->result[] = $doctor;
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
            ]
        ];
    }

    public function getName()
    {
        return 'Получить список врачей';
    }
}