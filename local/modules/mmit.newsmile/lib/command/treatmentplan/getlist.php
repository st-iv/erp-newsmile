<?


namespace Mmit\NewSmile\Command\TreatmentPlan;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam\Integer;
use Mmit\NewSmile\CommandParam\String;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\TreatmentPlanTable;

class GetList extends Base
{
    protected static $name = 'Получить список планов лечения';

    protected function doExecute()
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
            new Integer('offset', 'смещение выборки от начала'),
            new Integer('limit', 'ограничение количества'),
            new String('sort_by', 'поле для сортировки'),
            new String('sort_order', 'направление сортировки')
        ];
    }
}