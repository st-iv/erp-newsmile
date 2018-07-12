<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\TreatmentPlanTable,
    Mmit\NewSmile\TreatmentPlanItemTable,
    Mmit\NewSmile\DoctorTable;

class ToothListComponent extends \CBitrixComponent
{


	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->arResult['PARENT_TOOTH'] = [
            'TOP' => [
                18,
                17,
                16,
                15,
                14,
                13,
                12,
                11,
                21,
                22,
                23,
                24,
                25,
                26,
                27,
                28,
            ],
            'BOTTOM' => [
                48,
                47,
                46,
                45,
                44,
                43,
                42,
                41,
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
            'TOP' => [
                55,
                54,
                53,
                52,
                51,
                61,
                62,
                63,
                64,
                65,
            ],
            'BOTTOM' => [
                85,
                84,
                83,
                82,
                81,
                71,
                72,
                73,
                74,
                75,
            ]
        ];
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