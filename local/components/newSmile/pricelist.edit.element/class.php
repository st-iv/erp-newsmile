<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\TreatmentPlanTable,
    Mmit\NewSmile\TreatmentPlanItemTable,
    Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\ClinicTable;

class PatientCardTreatmentPlanComponent extends \CBitrixComponent
{


	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->getElementService($this->request['ID']);
        $this->getMeasureList();
	}

    /**
     * метод получает услугу
     *
     * @param $sectionID
     */
    protected function getElementService($elementID)
    {
        if (!empty($elementID)) {
            if (Loader::includeModule('iblock')) {
                $rsResult = CIBlockElement::GetList(
                    array(),
                    array(
                        'IBLOCK_ID' => $this->IblockID,
                        'ID' => $elementID
                    ),
                    false,
                    false,
                    array(
                        '*',
                        'PROPERTY_MINIMUM_PRICE',
                        'PROPERTY_MAXIMUM_PRICE',
                    )
                );
                if ($arResult = $rsResult->Fetch()) {
                    $this->arResult['ITEM'] = $arResult;
                }
            }
            if (Loader::includeModule('catalog')) {
                $rsPrice = PriceTable::getList(array(
                    'order' => [
                        'ID' => 'desc'
                    ],
                    'filter' => [
                        'PRODUCT_ID' => $elementID,
                        'CATALOG_GROUP_ID' => ClinicTable::getCatalogGroupFromClinic()
                    ]
                ));
                if ($arPrice = $rsPrice->fetch()) {
                    $this->arResult['ITEM']['PRICE'] = $arPrice['PRICE'];
                }
                $rsProduct = ProductTable::getList([
                    'filter' => [
                        'ID' => $elementID
                    ]
                ]);
                if ($arProduct = $rsProduct->fetch()) {
                    $this->arResult['ITEM']['MEASURE'] = $arProduct['MEASURE'];
                }
            }
        }

    }

    protected function getMeasureList()
    {
        $arResult = [];
        $arResult[] = [
            'ID' => 5,
            'NAME' => 'Штука'
        ];
        $arResult[] = [
            'ID' => Option::get('mmit.newsmile', "id_measure_tooth", '0'),
            'NAME' => 'Зуб'
        ];
        $arResult[] = [
            'ID' => Option::get('mmit.newsmile', "id_measure_jowl", '0'),
            'NAME' => 'Челюсть'
        ];
        $this->arResult['MEASURE'] = $arResult;

    }


    /**
     * Метод обрабатывает запрос
     *
     * @param $request
     */
    protected function requestResult($request)
    {
        if (Loader::includeModule('catalog') && Loader::includeModule('iblock')) {
            $isNewProduct = false;
            $idElement = $request['ID'];
            if (empty($idElement)) {
                $isNewProduct = true;
            }
            $arField = [];
            if (!empty($request['NAME'])) {
                $arField['NAME'] = $request['NAME'];
            }
            if (!empty($request['MIN_PRICE'])) {
                $arField['PROPERTY_VALUES']['MINIMUM_PRICE'] = $request['MIN_PRICE'];
            }
            if (!empty($request['MAX_PRICE'])) {
                $arField['PROPERTY_VALUES']['MAXIMUM_PRICE'] = $request['MAX_PRICE'];
            }
            if (!empty($arField)) {
                $element = new CIBlockElement();
                if (!empty($idElement)) {
                    $element->Update($idElement, $arField);
                } else {
                    $arField['IBLOCK_ID'] = $this->IblockID;
                    $arField['IBLOCK_SECTION_ID'] = $request['SECTION_ID'];
                    $arField['ACTIVE'] = 'Y';
                    $arField['CODE'] = \CUtil::translit($arField['NAME'], 'ru');
                    $idElement = $element->Add($arField);
                }
            }
            $arField = [];
            if (!empty($request['MEASURE'])) {
                $arField['MEASURE'] = intval($request['MEASURE']);
            }
            if (!empty($arField)) {
                /*echo '<pre>';
                print_r($arField);
                echo '</pre>';*/
                if (!$isNewProduct) {
                    ProductTable::update($idElement, $arField);
                } else {
                    $arField['ID'] = $idElement;
                    ProductTable::add($arField);
                }
            }
            $arField = [];
            if (!empty($request['PRICE'])) {
                $isNewPrice = true;
                $arField['PRICE'] = $request['PRICE'];
                $arField['PRICE_SCALE'] = $request['PRICE'];
                $arField['PRODUCT_ID'] = $idElement;
                $arField['CURRENCY'] = 'RUB';
                $arField['CATALOG_GROUP_ID'] = ClinicTable::getCatalogGroupFromClinic();
                $rsPrice = PriceTable::getList(array(
                    'order' => [
                        'ID' => 'desc'
                    ],
                    'filter' => [
                        'PRODUCT_ID' => $idElement,
                        'CATALOG_GROUP_ID' => ClinicTable::getCatalogGroupFromClinic()
                    ]
                ));
                if ($arPrice = $rsPrice->fetch()) {
                    if (floatval($arPrice['PRICE']) == floatval($arField['PRICE'])){
                        $isNewPrice = false;
                    }
                }

                if ($isNewPrice) {
                    /*echo '<pre>';
                    print_r($arField);
                    echo '</pre>';*/
                    if (!empty($idElement)) {
                        PriceTable::add($arField);
                    }
                }
            }
            if (!empty($request['action'])) {
                LocalRedirect('/price-list/');
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
            $this->IblockID = Option::get('mmit.newsmile', 'iblock_services');
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