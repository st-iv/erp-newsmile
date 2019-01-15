<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base,
    Mmit\NewSmile\Application,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile;

class GetUnitedList extends Base
{
    protected static $name = 'Получить список приемов';
    protected static $dateChangeRequests;

    protected function doExecute()
    {
        $this->result['visit_list'] = array_merge($this->getVisitRequests(), $this->getVisits());
        $sortOrder = $this->params['order'];

        /* сортировка */

        usort($this->result['visit_list'], function($visitA, $visitB) use ($sortOrder)
        {
            if($this->params['is_active'])
            {
                $sortByA = 'timestamp';
                $sortByB = 'timestamp';
            }
            else
            {
                $sortByA = ((($visitA['date'] === null) || $visitA['is_near_future']) ? 'create_timestamp' : 'timestamp');
                $sortByB = ((($visitB['date'] === null) || $visitB['is_near_future']) ? 'create_timestamp' : 'timestamp');
            }

            if(!$visitA[$sortByA] && $visitB[$sortByB])
            {
                $result = -1;
            }
            else if(!$visitB[$sortByB] && $visitA[$sortByA])
            {
                $result = 1;
            }
            else if($visitA[$sortByA] > $visitB[$sortByB])
            {
                $result = 1;
            }
            else if(($visitB[$sortByB] > $visitA[$sortByA]))
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
            unset($visit['timestamp']);
            unset($visit['create_timestamp']);
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
            $filter['STATUS'] = ($this->params['is_active'] ? 'WAITING' : 'CANCELED');
        }

        $dbVisitRequests = NewSmile\Visit\VisitRequestTable::getList([
            'filter' => $filter,
            'select' => [
                '*',
                'SERVICE_NAME' => 'SERVICE.NAME'
            ]
        ]);

        $statusesTitles = NewSmile\Visit\VisitRequestTable::getEnumVariants('STATUS');

        while($visitRequest = $dbVisitRequests->fetch())
        {
             $visitRequestInfo = [
                'id' => $visitRequest['ID'],
                'date' => $visitRequest['DATE'] ? $visitRequest['DATE']->format('d.m.Y H:i:s') : null,
                'doctor' => null,
                'is_active' => $visitRequest['STATUS'] == 'WAITING',
                'status' => $statusesTitles[$visitRequest['STATUS']],
                'status_code' => $visitRequest['STATUS'],
                'is_visit_request' => true,
                'is_near_future' => $visitRequest['NEAR_FUTURE'] == true,
                'is_date_change_queried' => null,
                'new_date' => null,
                'timestamp' => $visitRequest['DATE'] ? $visitRequest['DATE']->getTimestamp() : null,
                'create_timestamp' => $visitRequest['DATE_CREATE']->getTimestamp(),
                'date_create' => $visitRequest['DATE_CREATE']->format('d.m.Y H:i:s'),
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

        /* запрос информации по приёмам */

        $filter = [
            'PATIENT_ID' => Application::getInstance()->getUser()->getId()
        ];

        if(isset($this->params['is_active']))
        {

            if($this->params['is_active'])
            {
                $filter['!STATUS'] = 'CANCELED';
                $filter['>=TIME_END'] = new DateTime();
            }
            else
            {
                $filter[] = [
                    'LOGIC' => 'OR',
                    [
                        '<TIME_END' => new DateTime(),
                    ],
                    [
                        'STATUS' => 'CANCELED'
                    ]
                ];
            }
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
                'STATUS',
                'TIMESTAMP_X'
            ],
            'offset' => $offset
        ];

        if($limit)
        {
            $queryParams['limit'] = $limit;
        }

        $statusesTitles = NewSmile\Visit\VisitTable::getEnumVariants('STATUS');

        $dbVisit = NewSmile\Visit\VisitTable::getList($queryParams);

        /* подготовка выходного массива */

        while($visit = $dbVisit->fetch())
        {
            $dateChangeInfo = $this->getDateChangeInfo($visit['ID']);

            $result[] = [
                'id' => $visit['ID'],
                'date' => $visit['TIME_START']->format('d.m.Y H:i:s'),
                'doctor' => NewSmile\Helpers::getFio($visit, 'DOCTOR_'),
                'is_active' => ($visit['TIME_END']->getTimestamp() >= time()),
                'status' => $statusesTitles[$visit['STATUS']],
                'status_code' => $visit['STATUS'],
                'is_visit_request' => false,
                'service' => null,
                'is_near_future' => null,
                'is_date_change_queried' => $dateChangeInfo['IS_QUERIED'],
                'new_date' => $dateChangeInfo['NEW_DATE'],
                'timestamp' => $visit['TIME_START']->getTimestamp(),
                'date_create' => $visit['TIMESTAMP_X']->format('d.m.Y H:i:s'),
                'create_timestamp' => $visit['TIMESTAMP_X']->getTimestamp()
            ];
        }

        return $result;
    }

    /**
     * Возвращает дату, на которую запрошен перенос указанного приёма. Если
     * @param int $visitId - id приёма
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getDateChangeInfo($visitId)
    {
        if(!isset(static::$dateChangeRequests))
        {
            $dbChangeDateRequests = NewSmile\Visit\ChangeDateRequestTable::getList();
            static::$dateChangeRequests = [];

            while($changeDateRequest = $dbChangeDateRequests->fetch())
            {
                static::$dateChangeRequests[$changeDateRequest['VISIT_ID']] = [
                    'NEW_DATE' => ($changeDateRequest['NEW_DATE'] ? $changeDateRequest['NEW_DATE']->format('d.m.Y H:i:s') : null),
                    'IS_QUERIED' => true
                ];
            }
        }

        return static::$dateChangeRequests[$visitId] ?: [
            'NEW_DATE' => null,
            'IS_QUERIED' => false
        ];
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