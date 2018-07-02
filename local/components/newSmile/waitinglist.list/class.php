<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\WaitingListTable;

class WaitingListListComponent extends \CBitrixComponent
{

    /**
     * получение результатов
     */
    protected function getResult()
    {
        $rsWaitingList = WaitingListTable::getList(array(
            'select' => array(
                '*',
                'UF_PATIENT_' => 'PATIENT',
                'UF_DOCTOR_' => 'DOCTOR',
            )
        ));
        while ($arWaitingList = $rsWaitingList->fetch())
        {
            $this->arResult['ITEMS'][] = $arWaitingList;
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