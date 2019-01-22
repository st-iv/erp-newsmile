<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base,
    Mmit\NewSmile\Application,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile,
    Mmit\NewSmile\Command\Doctor;

class GetUnitedList extends Base
{
    protected static $name = 'Получить список приемов';
    protected static $dateChangeRequests;

    protected function doExecute()
    {
        $this->result['visit_list'] = array_merge($this->getVisitRequests(), $this->getVisits());

        /* сортировка */

        if($this->params['is_active'])
        {
            $this->sortActive($this->result['visit_list']);
        }
        else
        {
            $this->sortArchive($this->result['visit_list']);
        }


        /* list position and doctors */
        $doctors = $this->getDoctors($this->result['visit_list']);

        foreach ($this->result['visit_list'] as $index => &$visit)
        {
            $visit['list_position'] = $index;
            $visit['doctor'] = $doctors[$visit['doctor']];

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
        $result = []; return $result;


        $filter = [];

        if(isset($this->params['is_active']))
        {
            $filter['status'] = ($this->params['is_active'] ? 'WAITING' : 'CANCELED');
        }

        $getListCommand = new NewSmile\Command\VisitRequest\GetListMobile([
            'filter' => $filter
        ]);

        $getListCommand->execute();

        return $getListCommand->result['list'];
    }

    protected function sortActive(&$list)
    {
        $sortOrder = $this->params['order'];

        usort($list, function($visitA, $visitB) use ($sortOrder)
        {
            if(!$visitA['is_near_future'] && $visitB['is_near_future'])
            {
                $result = 1;
            }
            else if(!$visitB['is_near_future'] && $visitA['is_near_future'])
            {
                $result = -1;
            }
            else if(!$visitA['timestamp'] && $visitB['timestamp'])
            {
                $result = -1;
            }
            else if(!$visitB['timestamp'] && $visitA['timestamp'])
            {
                $result = 1;
            }
            else if($visitA['timestamp'] > $visitB['timestamp'])
            {
                $result = 1;
            }
            else if($visitA['timestamp'] < $visitB['timestamp'])
            {
                $result = -1;
            }
            else
            {
                $result = 0;
            }

            return $result * (($sortOrder == 'asc') ? 1 : -1);
        });
    }

    protected function sortArchive(&$list)
    {
        $sortOrder = $this->params['order'];

        usort($list, function($visitA, $visitB) use ($sortOrder)
        {
            $sortByA = ((($visitA['date'] === null) || $visitA['is_near_future']) ? 'create_timestamp' : 'timestamp');
            $sortByB = ((($visitB['date'] === null) || $visitB['is_near_future']) ? 'create_timestamp' : 'timestamp');

            if($visitA[$sortByA] > $visitB[$sortByB])
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

        $queryParams = [
            'filter' => $filter,
            'select' => [
                'ID',
                'TIME_START',
                'TIME_END',
                'DOCTOR_ID',
                'STATUS',
                'TIMESTAMP_X'
            ]
        ];

        $statusesTitles = NewSmile\Visit\VisitTable::getEnumVariants('STATUS');

        $dbVisit = NewSmile\Visit\VisitTable::getList($queryParams);

        /* подготовка выходного массива */

        while($visit = $dbVisit->fetch())
        {
            $dateChangeInfo = $this->getDateChangeInfo($visit['ID']);

            $result[] = [
                'id' => $visit['ID'],
                'date' => $visit['TIME_START']->format('d.m.Y H:i:s'),
                'doctor' => $visit['DOCTOR_ID'],
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

    protected function getDoctors($visits)
    {
        $doctorsIds = [];

        foreach ($visits as $visit)
        {
            if($visit['doctor'])
            {
                $doctorsIds[$visit['doctor']] = true;
            }
        }

        $doctorsListCommand = new Doctor\GetListMobile([
            'ids' => array_keys($doctorsIds),
            'get-specialization' => true
        ]);

        $doctorsListCommand->execute();
        $commandResult = $doctorsListCommand->getResult();
        $result = [];

        foreach ($commandResult['list'] as $doctor)
        {
            $result[$doctor['id']] = $doctor;
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
            ),
            new NewSmile\CommandParam\ArrayParam('')
        ];
    }
}