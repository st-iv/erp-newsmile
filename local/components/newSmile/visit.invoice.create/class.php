<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Catalog\PriceTable,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\TreatmentPlanTable,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\ClinicTable;
use Mmit\NewSmile\InvoiceItemTable;
use Mmit\NewSmile\InvoiceTable;

class VisitInvoiceCreateComponent extends \CBitrixComponent
{


	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->IblockID = Option::get('mmit.newsmile', 'iblock_services');
        $this->getSectionServices();
        $this->getElementServices($this->request['SECTION_ID']);
        $this->getInvoice();
	}

    /**
     *  Метод получает прием привязаный к счету
     */
    protected function getVisit($invoiceID)
    {
        $rsResult = VisitTable::getList([
            'filter' => [
                'ID' => $invoiceID,
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ],
            'select' => [
                '*',
                'PATIENT_NAME' => 'PATIENT.NAME',
                'DOCTOR_NAME' => 'DOCTOR.NAME',
            ]
        ]);
        if ($arResult = $rsResult->fetch()) {
            $this->arResult['VISIT'] = $arResult;
        }
    }

    /**
     *  Метод получает счет
     */
    protected function getInvoice()
    {
        $rsResult = InvoiceTable::getList([
            'filter' => [
                'ID' => $this->request['INVOICE_ID']
            ]
        ]);
        if ($arResult = $rsResult->fetch()) {
            $this->arResult['INVOICE'] = $arResult;
            $rsInvoiceItem = InvoiceItemTable::getList([
                'filter' => [
                    'INVOICE_ID' => $arResult['ID']
                ]
            ]);
            $arElementsID = [];
            while ($arInvoiceItem = $rsInvoiceItem->fetch())
            {
                $this->arResult['INVOICE']['ITEMS'][] = $arInvoiceItem;
                $arElementsID[] = $arInvoiceItem['PRODUCT_ID'];
            }
            $this->getElementServiceFromPlan($arElementsID);

            $this->getVisit($arResult['VISIT_ID']);
        }
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
        if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
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
            $arProductID = [];
            while ($arResult = $rsResult->Fetch()) {
                $this->arResult['ELEMENT_SERVICES'][$arResult['ID']] = $arResult;
                $arProductID[] = $arResult['ID'];
            }
            $rsPrice = PriceTable::getList(array(
                'filter' => [
                    'PRODUCT_ID' => $arProductID,
                    'CATALOG_GROUP_ID' => ClinicTable::getCatalogGroupFromClinic()

                ]
            ));
            while ($arPrice = $rsPrice->fetch()) {
                $this->arResult['ELEMENT_SERVICES'][$arPrice['PRODUCT_ID']]['PRICE'] = $arPrice;
            }
        }
    }

    /**
     *  метод получает информацию об услугах находящихся в счете
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
        if (isset($request['CREATE_INVOICE'])) {
            $rsInvoice = InvoiceTable::getList([
                'filter' => [
                    'VISIT_ID' => $request['VISIT_ID']
                ]
            ]);
            if ($arInvoice = $rsInvoice->fetch()) {
                header('Location: /invoice/?INVOICE_ID='.$arInvoice['ID']);
                die();
            } else {
                $idInvoice = InvoiceTable::add(['VISIT_ID' => $request['VISIT_ID']]);
                header('Location: /invoice/?INVOICE_ID='.$idInvoice);
                die();
            }
        }
        if (isset($request['ADD_ELEMENTS'])) {
            InvoiceTable::addItemToInvoice($request['INVOICE_ID'], $request['ELEMENT_ID'], $request['MEASURE']);
        }
        if (isset($request['CLOSE_VISIT'])) {
            $arFiled['STATUS_ID'] = VisitTable::STATUS_END;
            /*TODO доработать учет времени*/
            //$arFiled['TIME_END'] = new DateTime();

            if (!empty($arFiled)) {
                VisitTable::update($request['CLOSE_VISIT'], $arFiled);
            }
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