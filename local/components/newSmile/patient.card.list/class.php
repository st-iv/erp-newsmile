<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\Status,
    Mmit\NewSmile\VisitTable;

class PatientCardListComponent extends \CBitrixComponent
{

    /**
     * получение результатов
     */
    protected function getResult()
    {
        $rsResult = PatientCardTable::getList(array(
        ));
        while ($arResult = $rsResult->fetch())
        {
            $this->arResult['PATIENT_CARDS'][] = $arResult;
        }

        $this->getDoctors();
        $this->getStatusPatient();
    }

    protected function getStatusPatient()
    {
        $rsStatusPatient = Status\PatientTable::getList(array(
            'select' => array('ID', 'NAME')
        ));
        while ($arStatusPatient = $rsStatusPatient->fetch())
        {
            $this->arResult['STATUS_PATIENT'][] = $arStatusPatient;
        }
    }

    protected function getDoctors()
    {
        $rsDoctor = DoctorTable::getList(array(
            'filter' => [
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ],
            'select' => array('ID','NAME')
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][] = $arDoctor;
        }
    }
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
        if (!Loader::includeModule('mmit.newSmile')) die();
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