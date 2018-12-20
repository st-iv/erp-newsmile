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
        $filter = [
            'PATIENT_ID' => Application::getInstance()->getUser()->getId()
        ];

        $isActive = $this->params['is_active'];

        if(isset($isActive))
        {
            $filterKey = ($isActive == 'true' ? '>=' : '<') . 'TIME_END';
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
            'offset' => $offset,
            'count_total' => true
        ];

        if($limit)
        {
            $queryParams['limit'] = $limit;
        }

        $statusesTitles = NewSmile\VisitTable::getEnumVariants('STATUS');

        $dbVisit = NewSmile\VisitTable::getList($queryParams);

        while($visit = $dbVisit->fetch())
        {
            $this->result['visit_list'][] = [
                'id' => $visit['ID'],
                'date' => $visit['TIME_START']->format('d.m.Y H:i:s'),
                'doctor' => NewSmile\Helpers::getFio($visit, 'DOCTOR_'),
                'is_active' => ($visit['TIME_END']->getTimestamp() >= time()),
                'status' => $statusesTitles[$visit['STATUS']]
            ];
        }

        $this->result['total_count'] = $dbVisit->getCount();
    }

    public function getParamsMap()
    {
        return [
            new NewSmile\CommandParam\Integer('offset', 'смещение выборки от начала'),
            new NewSmile\CommandParam\Integer('limit', 'ограничение количества'),
            new NewSmile\CommandParam\Bool('is_active', 'флаг выборки только будущих приемов'),
        ];
    }
}