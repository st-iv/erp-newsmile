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

class EditSectionFromPriceListComponent extends \CBitrixComponent
{


	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->getElementService($this->request['ID']);
	}

    /**
     * метод получает услугу
     *
     * @param $sectionID
     */
    protected function getElementService($sectionID)
    {
        if (!empty($sectionID)) {
            if (Loader::includeModule('iblock')) {
                $rsResult = CIBlockSection::GetList(
                    array(),
                    array(
                        'IBLOCK_ID' => $this->IblockID,
                        'ID' => $sectionID
                    )
                );
                if ($arResult = $rsResult->Fetch()) {
                    $this->arResult['ITEM'] = $arResult;
                }
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
        if (Loader::includeModule('catalog') && Loader::includeModule('iblock')) {
            $idSection = $request['ID'];
            $arField = [];
            if (!empty($request['NAME'])) {
                $arField['NAME'] = $request['NAME'];
            }
            if (!empty($arField)) {
                $element = new CIBlockSection();
                if (!empty($idSection)) {
                    $element->Update($idSection, $arField);
                } else {
                    $arField['IBLOCK_ID'] = $this->IblockID;
                    $arField['IBLOCK_SECTION_ID'] = $request['SECTION_ID'];
                    $arField['ACTIVE'] = 'Y';
                    $arField['CODE'] = \CUtil::translit($arField['NAME'], 'ru');
                    $idSection = $element->Add($arField);
                }
            }
            if (!empty($request['action'])) {
                if ($request['action'] == 'delete') {
                    CIBlockSection::Delete($request['ID']);
                }
                LocalRedirect('/price-list/');
            }
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