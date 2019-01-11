<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base,
    Mmit\NewSmile\Application,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile;

class GetList extends Base
{
    protected static $name = 'Получить список приемов';

    protected function doExecute()
    {
        $this->result['visit_list'] = array_merge($this->getVisitRequests(), $this->getVisits());
        $sortOrder = $this->params['order'];

        /* сортировка */

        usort($this->result['visit_list'], function($visitA, $visitB) use ($sortOrder)
        {
            if((!$visitA['date'] && $visitB['date']) || ($visitA['date'] > $visitB['date']))
            {
                $result = 1;
            }
            else if((!$visitB['date'] && $visitA['date']) || ($visitB['date'] > $visitA['date']))
            {
                $result = -1;
            }
            else
            {
                $result = 0;
            }

            return $result * (($sortOrder == 'asc') ? 1 : -1);
        });

        /* list position */
        foreach ($this->result['visit_list'] as $index => &$visit)
        {
            $visit['list_position'] = $index;
        }

        unset($visit);

        /* total count */

        $this->result['total_count'] = count($this->result['visit_list']);

        /* offset и limit */

        $this->result['visit_list'] = array_slice($this->result['visit_list'], $this->params['offset'], $this->params['limit'] ?: null);
    }

    /**
     * Получает список записей на приём
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getVisitRequests()
    {
        $result = [];

        $filter = [
            'PATIENT_ID' => Application::getInstance()->getUser()->getId()
        ];

        if(isset($this->params['is_active']))
        {
            $filterKey = 'STATUS';

            if(!$this->params['is_active'])
            {
                $filterKey = '!' . $filterKey;
            }

            $filter[$filterKey] = 'WAITING';
        }

        $dbVisitRequests = NewSmile\VisitRequestTable::getList([
            'filter' => $filter,
            'select' => [
                '*',
                'SERVICE_NAME' => 'SERVICE.NAME'
            ]
        ]);

        $statusesTitles = NewSmile\VisitRequestTable::getEnumVariants('STATUS');

        while($visitRequest = $dbVisitRequests->fetch())
        {
             $visitRequestInfo = [
                'id' => 'request_' . $visitRequest['ID'],
                'date' => $visitRequest['DATE'] ? $visitRequest['DATE']->format('d.m.Y H:i:s') : null,
                'doctor' => null,
                'is_active' => $visitRequest['STATUS'] == 'WAITING',
                'status' => $statusesTitles[$visitRequest['STATUS']],
                'is_visit_request' => true,
                'is_near_future' => $visitRequest['NEAR_FUTURE'] == true,
             ];

             if($visitRequest['SERVICE_ID'])
             {
                 $visitRequestInfo['service'] = [
                     'id' => $visitRequest['SERVICE_ID'],
                     'name' => $visitRequest['SERVICE_NAME']
                 ];
             }
             else
             {
                 $visitRequestInfo['service'] = null;
             }

            $result[] = $visitRequestInfo;
        }

        return $result;
    }

    /**
     * Получает список приёмов
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getVisits()
    {
        $result = [];

        $filter = [
            'PATIENT_ID' => Application::getInstance()->getUser()->getId()
        ];

        if(isset($this->params['is_active']))
        {
            $filterKey = ($this->params['is_active'] ? '>=' : '<') . 'TIME_END';
            $filter[$filterKey] = new DateTime();
        }

        $limit = $this->params['limit'];
        $offset = $this->params['offset'] ?: 0;

        $queryParams = [
            'filter' => $filter,
            'select' => [
                'ID',
                'TIME_START',
                'TIME_END',
                'DOCTOR_NAME' => 'DOCTOR.NAME',
                'DOCTOR_LAST_NAME' =>'DOCTOR.LAST_NAME',
                'DOCTOR_SECOND_NAME' =>'DOCTOR.SECOND_NAME',
                'STATUS'
            ],
            'offset' => $offset
        ];

        if($limit)
        {
            $queryParams['limit'] = $limit;
        }

        $statusesTitles = NewSmile\VisitTable::getEnumVariants('STATUS');

        $dbVisit = NewSmile\VisitTable::getList($queryParams);

        while($visit = $dbVisit->fetch())
        {
            $result[] = [
                'id' => 'visit_' . $visit['ID'],
                'date' => $visit['TIME_START']->format('d.m.Y H:i:s'),
                'doctor' => NewSmile\Helpers::getFio($visit, 'DOCTOR_'),
                'is_active' => ($visit['TIME_END']->getTimestamp() >= time()),
                'status' => $statusesTitles[$visit['STATUS']],
                'is_visit_request' => false,
                'service' => null,
                'is_near_future' => null,
            ];
        }

        return $result;
    }

    public function getParamsMap()
    {
        return [
            new NewSmile\CommandParam\Integer(
                'offset',
                'смещение выборки от начала',
                '',
                false,
                0
            ),
            new NewSmile\CommandParam\Integer('limit', 'ограничение количества'),
            new NewSmile\CommandParam\Bool('is_active', 'флаг выборки только будущих приемов'),
            new NewSmile\CommandParam\String(
                'order',
                'порядок сортировки',
                    'asc - по возрастанию даты, desc - по убыванию даты',
                false,
                'asc'
            )
        ];
    }
}