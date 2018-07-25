<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\TreatmentPlanTable,
    Mmit\NewSmile\TreatmentPlanItemTable,
    Mmit\NewSmile\DoctorTable;

class PatientCardTreatmentPlanComponent extends \CBitrixComponent
{


	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->IblockID = Option::get('mmit.newsmile', 'iblock_services');
        $this->getSectionServicesAll();
        $this->getSectionServices($this->request['SECTION_ID']);
        $this->getElementServices($this->request['SECTION_ID']);
	}

    /**
     *  Метод получает список разделов Услуг
     */
    protected function getSectionServicesAll()
    {
        if (Loader::includeModule('iblock')) {
            $rsResult = CIBlockSection::GetList(
                array("left_margin"=>"asc"),
                array(
                    'IBLOCK_ID' => $this->IblockID
                ),
                false,
                array()
            );
            while ($arResult = $rsResult->Fetch()) {
                $this->arResult['SECTION_SERVICES_ALL'][] = $arResult;
            }
        }
    }

    /**
     *  Метод получает список разделов Услуг в указаном разделе
     */
    protected function getSectionServices($sectionID)
    {
        if (empty($sectionID)) {
            $sectionID = false;
        }
        if (Loader::includeModule('iblock')) {
            $rsResult = CIBlockSection::GetList(
                array("left_margin"=>"asc"),
                array(
                    'IBLOCK_ID' => $this->IblockID,
                    'SECTION_ID' => $sectionID
                ),
                false,
                array()
            );
            while ($arResult = $rsResult->Fetch()) {
                $this->arResult['SECTION_SERVICES'][] = $arResult;
            }
        }
    }

    /**
     * метод получает список услуг в указаном разделе
     *
     * @param $sectionID
     */
    protected function getElementServices($sectionID)
    {
        if (empty($sectionID)) {
            $sectionID = false;
        }
        if (Loader::includeModule('iblock')) {
            $rsResult = CIBlockElement::GetList(
                array(),
                array(
                    'IBLOCK_ID' => $this->IblockID,
                    'IBLOCK_SECTION_ID' => $sectionID
                ),
                false,
                false,
                array()
            );
            while ($arResult = $rsResult->Fetch()) {
                $this->arResult['ELEMENT_SERVICES'][] = $arResult;
            }
        }
    }


    /**
     * Метод обрабатывает запрос
     *
     * @param $request
     */
    protected function requestResult($request)
    {
        if (isset($request['CREATE_PLAN'])
            && !empty($this->arParams['PATIENT_ID'])
            && !empty($request['NAME'])) {
            $arField = array(
                'NAME' => $request['NAME']
            );
            if (!empty($request['DATE_START'])) {
                $arField['DATE_START'] = new Date($request['DATE_START'], 'Y-m-d');
            }
            $this->createTreatmentPlan(intval($this->arParams['PATIENT_ID']), $arField);
        }
        if (isset($request['ADD_ELEMENTS']) && !empty($request['MEASURE'])) {
            TreatmentPlanTable::addItemToTreatmentPlan($request['PLAN_ID'], $request['ELEMENT_ID'], $request['MEASURE']);
        }
    }

    /**
     * мотод создает новый план лечения
     *
     * @param $idPatientCard - ид карточки пациента
     * @param $arField - поля нового плана лечения
     */
    protected function createTreatmentPlan($idPatientCard, $arField)
    {
        global $USER;
        $arField['USER_CREATE_ID'] = $USER->GetID();
        $arField['USER_UPDATE_ID'] = $USER->GetID();
        $arField['PATIENT_ID'] = $idPatientCard;
        TreatmentPlanTable::add($arField);
    }

	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
        if (!Loader::includeModule('mmit.newSmile')) die();
		try
		{
		    $this->requestResult($this->request);
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