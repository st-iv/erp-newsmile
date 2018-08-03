<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\Status,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable;

class DoctorEditComponent extends \CBitrixComponent
{

    protected function requestResult($request)
    {
        $arFields = array();
        if (!empty($request['NAME'])) {
            $arFields['NAME'] = $request['NAME'];
        }
        if (!empty($request['CLINIC_ID'])) {
            $arFields['CLINIC_ID'] = intval($request['CLINIC_ID']);
        }

        if (!empty($arFields)) {
            if (!empty($this->arParams['ID'])) {
                DoctorTable::update($this->arParams['ID'], $arFields);
            } else {
                $res = DoctorTable::add($arFields);
                if ($res->isSuccess()) {
                    LocalRedirect('/doctors/edit/' . $res->getId() . '/');
                }
            }
        }
    }

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        if (!empty($this->arParams['ID'])) {
            $rsResult = DoctorTable::getList([
                'filter' => [
                    'ID' => $this->arParams['ID']
                ]
            ]);
            if ($arResult = $rsResult->fetch()) {
                $this->arResult['DOCTORS'] = $arResult;
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