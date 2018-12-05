<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile,
    Bitrix\Main\Entity\Query,
    Bitrix\Main\Entity\ExpressionField,
    Bitrix\Main\DB;

class CalendarComponent extends \CBitrixComponent
{
    const mktimeWeek = 604800;
    private $startDay = '';
    private $endDay = '';

    public function onPrepareComponentParams($arParams)
    {
        if(!$arParams['INITIAL_WEEKS_COUNT'])
        {
            $arParams['INITIAL_WEEKS_COUNT'] = 8;
        }

        if($arParams['FILTER'] instanceof \Bitrix\Main\Entity\Query\Filter\ConditionTree)
        {
            $arParams['FILTER'] = clone $arParams['FILTER'];
        }
        else
        {
            $arParams['FILTER'] = Query::filter();
        }

        return $arParams;
    }

    /**
	 * получение результатов
	 */
	protected function getResult()
	{
	    if (!Loader::includeModule('mmit.newSmile')) die();

        $this->initDateInterval();
        $this->arResult['START_DAY'] = date('Y-m-d', $this->startDay);
        $this->arResult['END_DAY'] = date('Y-m-d', $this->endDay);

        $rsSchedule = ScheduleTable::getList(array(
            'filter' => $this->getFilter(),
            'select' => array('ID', 'PATIENT_ID', 'TIME')
        ));

        $counter = array();

        while ($arSchedule = $rsSchedule->fetch())
        {
            if($arSchedule['TIME'])
            {
                /**
                 * @var Bitrix\Main\Type\DateTime $timeObject
                 */
                $timeObject = $arSchedule['TIME'];
                $date = $timeObject->format('Y-m-d');

                $counter[$date]['GENERAL'] += 1;

                if($arSchedule['PATIENT_ID'])
                {
                    $counter[$date]['ENGAGED'] += 1;
                    $counter[$date]['PATIENTS'][$arSchedule['PATIENT_ID']] = true;
                }
            }
        }

        foreach ($counter as $date => $countInfo)
        {
            $this->arResult['DATE'][$date] = array(
                'GENERAL_MINUTES' => (int)$countInfo['GENERAL'] * 15,
                'ENGAGED_MINUTES' => (int)$countInfo['ENGAGED'] * 15,
                'PATIENTS' => count($countInfo['PATIENTS']),
            );
        }
	}

    protected function getFilter()
    {
        /**
         * @var \Bitrix\Main\ORM\Query\Filter\ConditionTree $filter
         */
        $filter = $this->arParams['FILTER'];

        $filter->where('TIME', '>=', Bitrix\Main\Type\DateTime::createFromTimestamp($this->startDay));
        $filter->where('TIME', '<', Bitrix\Main\Type\DateTime::createFromTimestamp($this->endDay));
        $filter->whereNot('DOCTOR_ID', false);

        return $filter;
    }

    protected function initDateInterval()
    {
        $dateFromTs = (int)strtotime($this->request->getPost('DATE_FROM')) ?: time();
        $this->startDay = strtotime('Monday this week', $dateFromTs);


        if($this->request->getPost('DATE_TO'))
        {
            $dateToTs = strtotime($this->request->getPost('DATE_TO'));
        }
        else
        {
            $dateToTs = $this->startDay + static::mktimeWeek * $this->arParams['INITIAL_WEEKS_COUNT'];
        }

        $this->endDay = strtotime('Sunday this week', $dateToTs);
    }
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
		try
		{
			$this->getResult();
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>