<?

namespace Mmit\NewSmile\Command\Schedule;


use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Command;
use Mmit\NewSmile\ScheduleTable;
use Mmit\NewSmile;

class GetCalendar extends Command\Base
{
    /**
     * @var \DateTime
     */
    protected $dateFrom;

    /**
     * @var \DateTime
     */
    protected $dateTo;

    protected function doExecute()
    {
        $this->initDateInterval();
        $this->result['dateFrom'] = $this->dateFrom->format('Y-m-d');
        $this->result['dateTo'] = $this->dateTo->format('Y-m-d');

        $rsSchedule = ScheduleTable::getList(array(
            'filter' => $this->getFilter(),
            'select' => array('ID', 'PATIENT_ID', 'TIME', 'DURATION')
        ));

        $counter = array();

        while ($arSchedule = $rsSchedule->fetch())
        {
            if($arSchedule['TIME'])
            {
                /**
                 * @var DateTime $timeObject
                 */
                $timeObject = $arSchedule['TIME'];
                $date = $timeObject->format('Y-m-d');

                $counter[$date]['GENERAL'] += $arSchedule['DURATION'];

                if($arSchedule['PATIENT_ID'])
                {
                    $counter[$date]['ENGAGED'] += $arSchedule['DURATION'];
                    $counter[$date]['PATIENTS'][$arSchedule['PATIENT_ID']] = true;
                }
            }
        }

        foreach ($counter as $date => $countInfo)
        {
            $this->result['dateData'][$date] = array(
                'generalTime' => $countInfo['GENERAL'],
                'engagedTime' => $countInfo['ENGAGED'],
                'patientsCount' => count($countInfo['PATIENTS']),
            );
        }
    }

    protected function initDateInterval()
    {
        $this->dateFrom = new \DateTime($this->params['dateFrom']);
        $this->dateFrom->modify('Monday this week');
        $this->params['weeksCount'] = (int)$this->params['weeksCount'];

        if($this->params['dateTo'])
        {
            $this->dateTo = new \DateTime($this->params['dateTo']);
        }
        else
        {
            $this->dateTo = clone $this->dateFrom;
            $this->dateTo->modify('+' . $this->params['weeksCount'] . ' weeks');
            $this->dateTo->modify('Sunday this week');
        }
    }

    protected function getFilter()
    {
        /**
         * @var \Bitrix\Main\ORM\Query\Filter\ConditionTree $filter
         */
        $filter = Query::filter();

        $filter->where('TIME', '>=', DateTime::createFromPhp($this->dateFrom));
        $filter->where('TIME', '<', DateTime::createFromPhp($this->dateTo));
        $filter->whereNot('DOCTOR_ID', false);

        return $filter;
    }

    public function getParamsMap()
    {
        return [
            'dateFrom' => [
                'TITLE' => 'начальная дата',
                'DEFAULT' => date('Y-m-d')
            ],
            'dateTo' => [
                'TITLE' => 'конечная дата',
            ],
            'weeksCount' => [
                'TITLE' => 'количество запрашиваемых недель',
                'DEFAULT' => 8
            ]
        ];
    }
}