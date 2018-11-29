<?


namespace Mmit\NewSmile\Command\TreatmentPlan;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\TreatmentPlanTable;

class GetList extends Base
{
    public function execute()
    {
        $queryParams = [
            'select' => [
                'ID',
                'NAME',
                'DATE_CREATE'
            ],
            'filter' => [
                'PATIENT_ID' => Application::getInstance()->getUser()->getId()
            ],
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
                strtoupper($this->params['sort_by']) => $this->params['sort_order']
            ];
        }

        $dbTreatmentPlans = TreatmentPlanTable::getList($queryParams);

        $this->result['total_count'] = $dbTreatmentPlans->getCount();

        while($plan = $dbTreatmentPlans->fetch())
        {
            $plan['DATE_CREATE'] = $plan['DATE_CREATE']->format('d.m.Y');
            $this->result['plan_list'][] = Helpers::strtolowerKeys($plan);
        }
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
        return 'Получить список планов лечения';
    }
}