<?

namespace Mmit\NewSmile\Document;

use Mmit\NewSmile\TreatmentPlanTable;

class TreatmentPlan extends BaseDocument
{
    public function getName()
    {
        return 'План лечения';
    }

    public function getTemplateData()
    {
        $dbPlan = TreatmentPlanTable::getByPrimary($this->data['PLAN_ID'], [
            'select' => [
                '*',
                'PATIENT_NAME' => 'PATIENT.NAME',
                'PATIENT_LAST_NAME' => 'PATIENT.LAST_NAME',
                'PATIENT_SECOND_NAME' => 'PATIENT.SECOND_NAME',
                'ITEM_' => 'ITEMS',
                'SERVICE_GROUP_NAME' => 'ITEMS.SERVICE.GROUP.NAME',
                'SERVICE_GROUP_ID' => 'ITEMS.SERVICE.GROUP_ID',
                'SERVICE_NAME' => 'ITEMS.SERVICE.NAME',
            ]
        ]);

        $result = [];

        while($plan = $dbPlan->fetch())
        {
            foreach ($plan as $fieldName => $fieldValue)
            {
                if(preg_match('/ITEM_([A-Z0-9_]+)/', $fieldName, $matches)
                    || preg_match('/SERVICE_([A-Z0-9_]+)/', $fieldName, $matches))
                {
                    $result['GROUPS'][$plan['SERVICE_GROUP_ID']]['NAME'] = $plan['SERVICE_GROUP_NAME'];
                    $result['GROUPS'][$plan['SERVICE_GROUP_ID']]['ITEMS'][$plan['ITEM_ID']][$matches[1]] = $fieldValue;
                }
                else
                {
                    $result[$fieldName] = $fieldValue;
                }
            }
        }

        $result['MIN_SUM'] = 0;
        $result['MAX_SUM'] = 0;

        foreach ($result['GROUPS'] as &$group)
        {
            $group['MIN_SUM'] = 0;
            $group['MAX_SUM'] = 0;

            foreach ($group['ITEMS'] as $item)
            {
                $group['MIN_SUM'] += $item['MIN_PRICE'];
                $group['MAX_SUM'] += $item['MAX_PRICE'];
            }

            $result['MIN_SUM'] += $group['MIN_SUM'];
            $result['MAX_SUM'] += $group['MAX_SUM'];
        }

        unset($group);

        if(!$result)
        {
            throw new \Exception('Не найден план лечения с id ' . $this->data['PLAN_ID']);
        }

        return $result;
    }

    public function getParamsMap()
    {
        return [
            'PLAN_ID' => 'id плана лечения'
        ];
    }
}