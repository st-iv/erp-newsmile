<?


namespace Mmit\NewSmile\Command\Visit;

use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile;
use Mmit\NewSmile\Command\Doctor;
use Mmit\NewSmile\CommandVariable;

class GetListMobile extends Base
{
    protected static $dateChangeRequests;
    protected static $doctorsCache;

    public function getDescription()
    {
        return 'Получает список приёмов для текущего пользователя в особом формате для мобильного приложения';
    }

    public function getResultFormat()
    {
        return new NewSmile\Command\ResultFormat([
            (new NewSmile\CommandVariable\ArrayParam('list', 'список приёмов', true))->setContentType(
                (new NewSmile\CommandVariable\Object('', '', true))->setShape([
                    new CommandVariable\Integer('id', 'id', true),
                    new CommandVariable\String('date', 'дата приёма в формате DD.MM.YYYY HH:mm:SS', true),
                    new CommandVariable\Bool('is_active', 'флаг активности приёма', true),
                    new CommandVariable\String('status', 'название статуса приёма', true),
                    new CommandVariable\String('status_code', 'код статуса приёма', true),
                    new CommandVariable\Bool('is_date_change_queried', 'флаг запроса на перенос приёма', true),
                    new CommandVariable\String('new_date', 'новая дата', true),
                    new CommandVariable\Integer('timestamp', 'timestamp времени начала приёма', true),
                    new CommandVariable\String('date_create', 'дата создания приёма в формате DD.MM.YYYY HH:mm:SS', true),
                    new CommandVariable\Integer('create_timestamp', 'timestamp времени создания приёма', true),
                    new CommandVariable\Object('doctor', 'информация о враче', true)
                ])
            )
        ]);
    }

    protected function doExecute()
    {
        /* запрос информации по приёмам */


        $filter = [
            'PATIENT_ID' => NewSmile\Application::getInstance()->getUser()->getId(),
        ];

        if($this->params['ids'])
        {
            $filter['ID'] = $this->params['ids'];
        }

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

        $doctorsIds = [];

        /* подготовка выходного массива */

        while($visit = $dbVisit->fetch())
        {
            $dateChangeInfo = $this->getDateChangeInfo($visit['ID']);
            $doctorsIds[] = $visit['DOCTOR_ID'];

            $this->result['list'][] = [
                'id' => $visit['ID'],
                'date' => $visit['TIME_START']->format('d.m.Y H:i:s'),
                'doctor' => $visit['DOCTOR_ID'],
                'is_active' => ($visit['TIME_END']->getTimestamp() >= time()),
                'status' => $statusesTitles[$visit['STATUS']],
                'status_code' => $visit['STATUS'],
                'is_date_change_queried' => $dateChangeInfo['IS_QUERIED'],
                'new_date' => $dateChangeInfo['NEW_DATE'],
                'timestamp' => $visit['TIME_START']->getTimestamp(),
                'date_create' => $visit['TIMESTAMP_X']->format('d.m.Y H:i:s'),
                'create_timestamp' => $visit['TIMESTAMP_X']->getTimestamp()
            ];
        }

        /* информация о врачах */

        $doctors = static::getDoctors(array_unique($doctorsIds));

        foreach ($this->result['list'] as &$visit)
        {
            $visit['doctor'] = $doctors[$visit['doctor']];
        }

        unset($visit);
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

    public static function getDoctors($doctorsIds)
    {
        $doctorsListCommand = new Doctor\GetListMobile([
            'ids' => $doctorsIds,
            'get-specialization' => true
        ]);

        $doctorsListCommand->execute();
        $commandResult = $doctorsListCommand->getResult();
        $result = [];

        foreach ($commandResult['list'] as $doctor)
        {
            unset($doctor['specialization_code']);
            $result[$doctor['id']] = $doctor;
        }

        return $result;
    }

    public function getParamsMap()
    {
        return [
            GetUnitedList::getParam('is_active'),
            new NewSmile\CommandVariable\ArrayParam('ids', 'список id запрашиваемых заявок на приём', false, [])
        ];
    }


}