<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile\WorkChairTable;

class VisitAddComponent extends \CBitrixComponent
{

    /**
     * обработка результатов
     */
    protected function requestResult()
    {
        $arFiled = array();
        if (!empty($this->request['NAME'])) {
            $arFiled['NAME'] = $this->request['NAME'];
        }
        if (!empty($this->request['DATA'])) {
            $date = new Date($this->request['DATA'], 'Y-m-d');
            $arFiled['DATE_START'] = $date;

            if (!empty($this->request['TIME_START'])) {
                $timeStart = new DateTime($this->request['DATA'] . ' ' . date('H:i',strtotime($this->request['TIME_START'])), 'Y-m-d H:i');
                $arFiled['TIME_START'] = $timeStart;
            }
            if (!empty($this->request['TIME_END'])) {
                $timeEnd = new DateTime($this->request['DATA'] . ' ' . date('H:i',strtotime($this->request['TIME_END'])), 'Y-m-d H:i');
                $arFiled['TIME_END'] = $timeEnd;
            }
        }
        if (!empty($this->request['PATIENT_ID'])) {
            $arFiled['PATIENT_ID'] = $this->request['PATIENT_ID'];
        }
        if (!empty($this->request['DOCTOR_ID'])) {
            $arFiled['DOCTOR_ID'] = $this->request['DOCTOR_ID'];
            $arFiled['CLINIC_ID'] = 1;
        }
        if (!empty($this->request['WORK_CHAIR_ID'])) {
            $arFiled['WORK_CHAIR_ID'] = $this->request['WORK_CHAIR_ID'];
        }

        if (!empty($arFiled)) {
            try {
                $rsSchedule = ScheduleTable::getList(array(
                    'filter' => array(
                        '>=TIME' => $arFiled['TIME_START'],
                        '<TIME' => $arFiled['TIME_END'],
                        'WORK_CHAIR_ID' => $arFiled['WORK_CHAIR_ID'],
                        'WORK' => 'Y',
                        'ENGAGED' => 'N'
                    )
                ));
                if ($rsSchedule->getSelectedRowsCount() > 0) {
                    VisitTable::add($arFiled);
                    while ($arSchedule = $rsSchedule->fetch())
                    {
                        ScheduleTable::update($arSchedule['ID'], array(
                            'ENGAGED' => 'Y'
                        ));
                    }
                }else {
                    ShowError('Данное время занято или не рабочее');
                }
            } catch (Exception $e) {
                ShowError($e->getMessage());
            }
        }
    }
	
	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $rsWorkChair = WorkChairTable::getList();
        while ($arWorkChair = $rsWorkChair->fetch())
        {
            $this->arResult['WORK_CHAIR'][] = $arWorkChair;
        }

        $rsPatientCard = PatientCardTable::getList();
        while ($arPatientCard = $rsPatientCard->fetch())
        {
            $this->arResult['PATIENT'][] = $arPatientCard;
        }

        $rsDoctor = DoctorTable::getList();
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTOR'][] = $arDoctor;
        }
	}
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
		try
		{
            if (!Loader::includeModule('mmit.newSmile')) die();
            $this->requestResult();
			$this->getResult();
//            echo '<pre>';
//            print_r($this->arResult);
//            echo '</pre>';
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>