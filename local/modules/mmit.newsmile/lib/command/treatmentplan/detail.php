<?

namespace Mmit\NewSmile\Command\TreatmentPlan;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Service\ServiceTable;
use Mmit\NewSmile\TreatmentPlanItemTable;
use Mmit\NewSmile\TreatmentPlanTable;
use Mmit\NewSmile\CommandVariable;

class Detail extends Base
{
    public function getDescription()
    {
        return 'Получает детальную информацию по плану лечения с указанным id для текущего пользователя. Текущий пользователь должен быть пациентом.';
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            new CommandVariable\Integer('id', 'id',true),
            new CommandVariable\String('name', 'название', true),
            new CommandVariable\Date('date_create', 'дата создания в формате DD.MM.YYYY', true),
            (new CommandVariable\Object('plan_sum', 'сумма плана лечения', true))->setShape([
                new Integer('min', 'минимальная сумма', true),
                new Integer('max', 'максимальная сумма', true)
            ]),
            (new CommandVariable\ArrayParam('category_list', 'список групп услуг, входящих в план лечения'))->setContentType(
                (new CommandVariable\Object('', '', true))->setShape([
                    new Integer('id', 'id группы', true),
                    new CommandVariable\String('name', 'название', true),
                    (new CommandVariable\Object('plan_sum', 'сумма плана лечения по данной группе', true))->setShape([
                        new Integer('min', 'минимальная сумма', true),
                        new Integer('max', 'максимальная сумма', true)
                    ]),
                    (new CommandVariable\ArrayParam('service_list', 'список услуг', true))->setContentType(
                        (new CommandVariable\Object('', '', true))->setShape([
                            new Integer('id', 'id', true),
                            new CommandVariable\String('name', 'название', true),
                            (new CommandVariable\Object('plan_sum', 'сумма плана лечения по данной услуге', true))->setShape([
                                new Integer('min', 'минимальная сумма', true),
                                new Integer('max', 'максимальная сумма', true)
                            ]),
                            (new CommandVariable\ArrayParam('tooth_list', 'список целей применения услуг (зуб, челюсть и т. п.)', true))->setContentType(
                                (new CommandVariable\Object('', '', true))->setShape([
                                    new CommandVariable\String('name', 'код цели (например, номер зуба, код челюсти)', true),
                                    (new CommandVariable\Object('plan_sum', 'сумма плана лечения по данной цели', true))->setShape([
                                        new Integer('min', 'минимальная сумма', true),
                                        new Integer('max', 'максимальная сумма', true)
                                    ]),
                                ])
                            )
                        ])
                    )
                ])
            ),
        ]);
    }

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
            new Integer('id', 'id плана лечения', true)
        ];
    }
}