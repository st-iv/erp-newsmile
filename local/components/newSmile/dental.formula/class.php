<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\TreatmentPlanTable,
    Mmit\NewSmile\TreatmentPlanItemTable,
    Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Status\ToothTable;

class DentalFormulaComponent extends \CBitrixComponent
{


	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->arResult['PARENT_TOOTH'] = [
            'TOP_LEFT' => [
                18,
                17,
                16,
                15,
                14,
                13,
                12,
                11,
            ],
            'TOP_RIGHT' => [
                21,
                22,
                23,
                24,
                25,
                26,
                27,
                28,
            ],
            'BOTTOM_LEFT' => [
                48,
                47,
                46,
                45,
                44,
                43,
                42,
                41,
            ],
            'BOTTOM_RIGHT' => [
                31,
                32,
                33,
                34,
                35,
                36,
                37,
                38,
            ]
        ];
        $this->arResult['CHILD_TOOTH'] = [
            'TOP_LEFT' => [
                '',
                '',
                '',
                55,
                54,
                53,
                52,
                51,
            ],
            'TOP_RIGHT' => [
                61,
                62,
                63,
                64,
                65,
                '',
                '',
                '',
            ],
            'BOTTOM_LEFT' => [
                '',
                '',
                '',
                85,
                84,
                83,
                82,
                81,
            ],
            'BOTTOM_RIGHT' => [
                71,
                72,
                73,
                74,
                75,
                '',
                '',
                '',
            ]
        ];

        $this->getStatus();
	}

	protected function getStatus()
    {
        $rsResult = ToothTable::getList();
        while ($arResult = $rsResult->fetch())
        {
            $this->arResult['STATUS'][] = $arResult;
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