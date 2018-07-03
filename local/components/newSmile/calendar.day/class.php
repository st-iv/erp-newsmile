<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Config\Option,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\WorkChairTable;

class CalendarDayComponent extends \CBitrixComponent
{
    protected $thisDate = '';
	
	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->thisDate = date('Y-m-d');
	    if (!empty($this->request['THIS_DATE'])) {
            $this->thisDate = $this->request['THIS_DATE'];
        }
        $this->arResult['THIS_DATE'] = $this->thisDate;

        $isNext = $this->getWorkChair();
        $isNext = $isNext && $this->getSchedule();
        $isNext = $isNext && $this->getVisit();
//        $isNext = $isNext && $this->getDoctor();
	}

    protected function getSchedule()
    {
        $isResult = false;
        $rsSchedule = ScheduleTable::getList(array(
            'order' => array(
                'TIME' => 'ASC'
            ),
            'filter' => array(
                '>=TIME' => new Date($this->thisDate, 'Y-m-d'),
                '<=TIME' => new Date(date('Y-m-d', strtotime('tomorrow', strtotime($this->thisDate))), 'Y-m-d'),
                'CLINIC_ID' => 1
            ),
            'select' => array(
                'ID',
                'TIME',
                'UF_DOCTOR_' => 'DOCTOR',
                'UF_MAIN_DOCTOR_' => 'MAIN_DOCTOR',
                'WORK_CHAIR_ID',
                'CLINIC_ID',
                'ENGAGED'
            )
        ));
        while ($arSchedule = $rsSchedule->fetch())
        {
            $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['SCHEDULES'][] = $arSchedule;
            if (empty($this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['DOCTORS'][$arSchedule['UF_DOCTOR_ID']])) {
                $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['DOCTORS'][$arSchedule['UF_DOCTOR_ID']] = array(
                    'NAME' => $arSchedule['DOCTOR_NAME']
                );
            }

            if ($arSchedule['TIME']->getTimestamp() < strtotime($arSchedule['TIME']->format('Y-m-d 15:00'))) {
                $time = $arSchedule['TIME']->format('Y-m-d 9:00');
            } else {
                $time = $arSchedule['TIME']->format('Y-m-d 15:00');
            }
            if (!empty($arSchedule['UF_MAIN_DOCTOR_ID'])) {
                $tempField = array(
                    'ID' => $arSchedule['UF_MAIN_DOCTOR_ID'],
                    'NAME' => $arSchedule['UF_MAIN_DOCTOR_NAME'],
                    'TIME' => $time
                );
                if (array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === null ||
                    array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === false) {
                    $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS'][] = $tempField;
                }
            } else {
                $tempField = array(
                    'ID' => 0,
                    'TIME' => $time
                );
                if (array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === null ||
                    array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === false) {
                    $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS'][] = $tempField;
                }
            }

            $isResult = true;
        }
        return $isResult;
    }

	protected function getVisit()
    {
        $isResult = false;
        $rsVisit = VisitTable::getList(array(
            'order' => array(
                'TIME_START' => 'ASC'
            ),
            'filter' => array(
                'DATE_START' => new Date($this->thisDate, 'Y-m-d')
            ),
            'select' => array(
                'ID',
                'TIME_START',
                'TIME_END',
                'UF_PATIENT_' => 'PATIENT',
                'UF_DOCTOR_' => 'DOCTOR',
                'WORK_CHAIR_ID',
            )
        ));
        while ($arVisit = $rsVisit->Fetch())
        {
            $this->arResult['WORK_CHAIR'][$arVisit['WORK_CHAIR_ID']]['VISITS'][] = $arVisit;
            $isResult = true;
        }
        return $isResult;
    }

    protected function getWorkChair()
    {
        $isResult = false;
        $rsWorkChair = WorkChairTable::getList();
        while ($arWorkChair = $rsWorkChair->Fetch())
        {
            $this->arResult['WORK_CHAIR'][$arWorkChair['ID']] = $arWorkChair;
            $isResult = true;
        }
        return $isResult;
    }

    protected function getDoctor()
    {
        $rsDoctor = DoctorTable::getList(array(
            'filter' => array(
                'ID' => array_merge($this->arResult['DOCTOR_ID'], $this->arResult['MAIN_DOCTOR_ID'])
            )
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            if (array_search($arDoctor['ID'], $this->arResult['DOCTOR_ID'])) {
                $this->arResult['DOCTOR_ID'][$arDoctor['ID']] = $arDoctor;
            }
            if (array_search($arDoctor['ID'], $this->arResult['MAIN_DOCTOR_ID'])) {
                $this->arResult['MAIN_DOCTOR_ID'][$arDoctor['ID']] = $arDoctor;
            }
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