<?

namespace Mmit\NewSmile\Command\TreatmentPlan;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Service\ServiceTable;
use Mmit\NewSmile\TreatmentPlanItemTable;
use Mmit\NewSmile\TreatmentPlanTable;

class Detail extends Base
{
    protected static $name = 'Получить детальную информацию по плану лечения';

    protected function doExecute()
    {
        $serviceTree = ServiceTable::get1LvlTree();
        $services = [];

        foreach ($serviceTree as $group)
        {
            foreach ($group['SERVICES'] as $service)
            {
                $service['GROUP_ID'] = $group['ID'];
                $services[$service['ID']] = $service;
            }
        }

        $plan = $this->getPlan($this->params['id']);

        if(!$plan)
        {
            $this->setError('Для пациента не найден план с указанным id', 'TREATMENT_PLAN_NOT_FOUND');
            return;
        }

        $planSumMax = 0;
        $planSumMin = 0;

        $itemsList = [];
        $teethSum = $this->getTeethPlanSum($plan['ITEMS']);

        foreach ($plan['ITEMS'] as $item)
        {
            $service = $services[$item['SERVICE_ID']];
            $serviceGroup = $serviceTree[$service['GROUP_ID']];

            $itemListRecord =& $itemsList[$service['GROUP_ID']];
            $itemListRecord['name'] = $serviceGroup['NAME'];
            $itemListRecord['id'] = $service['GROUP_ID'];
            $itemListRecord['plan_sum']['max'] += $item['MAX_PRICE'];
            $itemListRecord['plan_sum']['min'] += $item['MIN_PRICE'];
            $planSumMax += $item['MAX_PRICE'];
            $planSumMin += $item['MIN_PRICE'];

            unset($service['GROUP_ID']);

            $service = Helpers::strtolowerKeys($service);

            $service['plan_sum']['max'] += $item['MAX_PRICE'];
            $service['plan_sum']['min'] += $item['MIN_PRICE'];

            foreach ($item['TARGET'] as $toothNumber)
            {
                $service['tooth_list'][] = [
                    'name' => $toothNumber,
                    'plan_sum' => [
                        'min' => $teethSum[$toothNumber]['plan_sum']['min'],
                        'max' => $teethSum[$toothNumber]['plan_sum']['max'],
                    ]
                ];
            }

            $itemListRecord['service_list'][] = $service;

            unset($itemListRecord);
        }

        $this->result = [
            'id' => $plan['ID'],
            'name' => $plan['NAME'],
            'date_create' => $plan['DATE_CREATE']->format('d.m.Y'),
            'plan_sum' => [
                'max' => $planSumMax,
                'min' => $planSumMin
            ],
            'category_list' => array_values($itemsList)
        ];
    }

    protected function getTeethPlanSum(array $planItems)
    {
        $result = [];

        foreach ($planItems as $item)
        {
            foreach ($item['TARGET'] as $toothNumber)
            {
                $result[$toothNumber]['plan_sum']['max'] += $item['MAX_PRICE'];
                $result[$toothNumber]['plan_sum']['min'] += $item['MIN_PRICE'];
            }
        }

        return $result;
    }

    protected function getPlan($id)
    {
        $dbPlan = TreatmentPlanTable::getByPrimary($id, [
            'filter' => [
                'PATIENT_ID' => Application::getInstance()->getUser()->getId()
            ],
            'select' => ['ID', 'NAME', 'DATE_CREATE']
        ]);

        $plan = $dbPlan->fetch();

        if($plan)
        {
            $dbItems = TreatmentPlanItemTable::getList([
                'filter' => [
                    'PLAN_ID' => $id
                ]
            ]);

            while($item = $dbItems->fetch())
            {
                $plan['ITEMS'][] = $item;
            }
        }

        return $plan ?: [];
    }

    public function getParamsMap()
    {
        return [
            'id' => [
                'TITLE' => 'id плана лечения',
                'REQUIRED' => true
            ]
        ];
    }
}