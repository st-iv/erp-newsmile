<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\VisitTable;

class CalendarComponent extends \CBitrixComponent
{
    const mktimeWeek = 604800;
    private $startDay = '';
    private $endDay = '';

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
	    $currentDate = time();
	    if (!empty($this->request['nextWeek'])) {
            $currentDate += $this->request['nextWeek'] * self::mktimeWeek;
        }
	    $this->arResult['CALENDAR'] = $this->setCalendar($currentDate);
	    $this->arResult['LINK_NEXT'] = '<a href="?nextWeek=' . ($this->request['nextWeek'] + 1) . '">&gt;&gt;&gt;</a>';
	    $this->arResult['LINK_PREV'] = '<a href="?nextWeek=' . ($this->request['nextWeek'] - 1) . '">&lt;&lt;&lt;</a>';
	    if (!Loader::includeModule('mmit.newSmile')) die();

        $rsSchedule = VisitTable::getCountVisitFromDate(array(
            ">=DATE_START" => $this->startDay,
            "<=DATE_START" => $this->endDay
        ));
        while ($arSchedule = $rsSchedule->Fetch())
        {
            $this->arResult[] = $arSchedule;
            $this->arResult['DATE'][$arSchedule['DATE_START']] = $arSchedule['COUNT'];
        }
	}

	protected function setCalendar($intCurrentDate)
    {
        $arResult = array();
        $currentMonday = strtotime('Monday this week', $intCurrentDate);
        $this->startDay = date('Y.m.d', $currentMonday);
        for ($w = 0; $w < 5; $w++) {
            for ($d = 0; $d < 7; $d++) {
                $difference = mktime(0,0,0,0,$d + $w * 7,0) - mktime(0,0,0,0,0,0);
                $arResult[$w][$d] = date('Y-m-d', $currentMonday + $difference );
            }
        }
        $this->endDay = date('Y.m.d', $currentMonday + $difference);

        return $arResult;
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