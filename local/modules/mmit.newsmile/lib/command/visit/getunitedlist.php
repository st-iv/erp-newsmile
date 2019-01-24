<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base,
    Mmit\NewSmile\Application,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile,
    Mmit\NewSmile\Command\Doctor;
use Mmit\NewSmile\Command;

class GetUnitedList extends Base
{
    protected static $name = 'Получить список приемов';
    protected static $dateChangeRequests;

    protected function doExecute()
    {
        /* запрос списка приёмов */

        $getVisitsCommand = new GetListMobile(['is_active' => $this->params['is_active']]);
        $getVisitsCommand->execute();
        $visitsResult = $getVisitsCommand->getResult();

        /* приведение списка приёмов к общему виду */

        array_walk($visitsResult['list'], function(&$item)
        {
            $item['is_visit_request'] = false;
            $item['service'] = null;
            $item['is_near_future'] = null;
        });

        /* запрос списка заявок на приём */

        $getVisitRequestsCommand = new Command\VisitRequest\GetListMobile(['is_active' => $this->params['is_active']]);
        $getVisitRequestsCommand->execute();
        $visitRequestsResult = $getVisitRequestsCommand->getResult();

        /* приведение списка заявок на приём к общему виду */

        array_walk($visitRequestsResult['list'], function(&$item)
        {
            $item['is_visit_request'] = true;
            $item['is_date_change_queried'] = null;
            $item['new_date'] = null;
        });

        /* объединение списков */

        $this->result['visit_list'] = array_merge($visitsResult['list'], $visitRequestsResult['list']);

        /* сортировка */

        if($this->params['is_active'])
        {
            $this->sortActive($this->result['visit_list']);
        }
        else
        {
            $this->sortArchive($this->result['visit_list']);
        }


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