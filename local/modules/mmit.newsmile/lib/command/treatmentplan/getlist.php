<?


namespace Mmit\NewSmile\Command\TreatmentPlan;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\CommandVariable\ArrayParam;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\CommandVariable\String;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\TreatmentPlanTable;
use Mmit\NewSmile\CommandVariable;

class GetList extends Base
{
    public function getDescription()
    {
        return 'Получает список планов лечения текущего пользователя. Текущий пользователь должен быть пациентом.';
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            (new ArrayParam('plan_list', 'список планов лечения', true))->setContentType(
                (new CommandVariable\Object('', '', true))->setShape([
                    new Integer('id', 'id', true),
                    new String('name', 'название', true),
                    new String('date_create', 'дата создания в формате DD.MM.YYYY', true)
                ])
            ),
            new Integer('total_count', 'общее количество планов лечения', true)
        ]);
    }

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