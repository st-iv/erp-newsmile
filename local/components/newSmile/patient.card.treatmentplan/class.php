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
        $arTreatmentPlanID = array();
        $rsTreatmentPlan = TreatmentPlanTable::getList(array(
            'filter' => array(
                'PATIENT_ID' => $this->arParams['PATIENT_ID']
            )
        ));
        while ($arTreatmentPlan = $rsTreatmentPlan->fetch())
        {
            $arTreatmentPlanID[] = $arTreatmentPlan['ID'];
            $this->arResult['TREATMENT_PLAN'][$arTreatmentPlan['ID']] = $arTreatmentPlan;
        }

        $rsTreatmentItemPlan = TreatmentPlanItemTable::getList(array(
            'filter' => array(
                'PLAN_ID' => $arTreatmentPlanID
            )
        ));

        $arTreatmentItemPlanProductID = array();
        while ($arTreatmentItemPlan = $rsTreatmentItemPlan->fetch())
        {

            $this->arResult['TREATMENT_PLAN'][$arTreatmentItemPlan['PLAN_ID']]['ITEMS'][] = $arTreatmentItemPlan;
            $arTreatmentItemPlanProductID[] = $arTreatmentItemPlan['PRODUCT_ID'];
        }
        $this->getElementServiceFromPlan($arTreatmentItemPlanProductID);

        $this->IblockID = Option::get('mmit.newsmile', 'iblock_services');
        $this->getSectionServices();
        $this->getElementServices($this->request['SECTION_ID']);
	}

    /**
     *  Метод получает список разделов Услуг
     */
    protected function getSectionServices()
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
     *  метод получает информацию об услугах находящихся в плане лечения
     *
     * @param $arElementID - элементы входящие в план
     */
    protected function getElementServiceFromPlan($arElementID)
    {
        if (Loader::includeModule('iblock')) {
            $rsResult = CIBlockElement::GetList(
                array(),
                array(
                    'ID' => $arElementID,
                ),
                false,
                false,
                array('ID','NAME')
            );
            while ($arResult = $rsResult->Fetch()) {
                $this->arResult['ELEMENTS'][$arResult['ID']] = $arResult;
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