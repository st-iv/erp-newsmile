<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\TreatmentPlanTable,
    Mmit\NewSmile\TreatmentPlanItemTable,
    Mmit\NewSmile\DoctorTable;

class ToothListComponent extends \Mmit\NewSmile\Component\AdvancedComponent
{
    protected $adultTeeth = [
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

    protected $childTeeth = [
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

    protected function getTeeth()
    {
        $result = [];

        $selectedTeeth = array_flip($this->arParams['SELECTED_TEETH']);

        foreach (['ADULT', 'CHILD'] as $toothGroup)
        {
            $fullSet = ($toothGroup == 'ADULT') ? $this->adultTeeth : $this->childTeeth;

            foreach ($fullSet as $jaw => $teeth)
            {
                foreach ($teeth as $toothNum)
                {
                    $isSelected = isset($selectedTeeth[$toothNum]);

                    $result[$toothGroup]['JAWS'][$jaw][$toothNum] = [
                        'SELECTED' => $isSelected
                    ];

                    if($isSelected)
                    {
                        $result[$toothGroup]['SELECTED_COUNT']++;
                    }

                }
            }
        }

        $maxSelectedCount = 0;

        foreach ($result as $toothGroup)
        {
            if($maxSelectedCount < $toothGroup['SELECTED_COUNT'])
            {
                $maxSelectedCount = $toothGroup['SELECTED_COUNT'];
            }
        }

        foreach ($result as &$toothGroup)
        {
            $toothGroup['SELECTED'] = ($toothGroup['SELECTED_COUNT'] == $maxSelectedCount);
            if($toothGroup['SELECTED'])
            {
                // ибо нам нужен только один selected
                $maxSelectedCount = -1;
            }
        }

        unset($toothGroup);


        return $result;
    }

	/**
	 * выполняет логику работы компонента
	 */
	public function execute()
	{
        if (!Loader::includeModule('mmit.newSmile')) die();
		try
		{
			$this->arResult['TEETH'] = $this->getTeeth();
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>