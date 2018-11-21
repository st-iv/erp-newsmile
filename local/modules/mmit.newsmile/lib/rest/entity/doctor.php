<?

namespace Mmit\NewSmile\Rest\Entity;

use Mmit\NewSmile\DoctorSpecializationTable;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Scheduler;
use Mmit\NewSmile\ScheduleTemplateTable;

class Doctor extends Controller
{
    protected $doctorsSchedule;

    protected function processList()
    {
        $offset = $this->getParam('offset');
        $limit = $this->getParam('limit');
        $sortBy = $this->getParam('sort_by');
        $sortOrder = $this->getParam('sort_order');

        $queryParams = [
            'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'ID']
        ];

        if($offset)
        {
            $queryParams['offset'] = $offset;
        }

        if($limit)
        {
            $queryParams['limit'] = $limit;
        }

        if($sortBy && $sortOrder)
        {
            $queryParams['order'] = [
                $sortBy => $sortOrder
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

            $this->responseData[] = $doctor;
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


    protected function getActionsMap()
    {
        return [
            'list' => [
                'PARAMS' => [
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
                ]
            ]
        ];
    }
}