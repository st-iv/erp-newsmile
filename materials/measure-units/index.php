<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Единицы измерения");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "elements_only",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\MeasureUnitTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/materials/measure-units/',
        'LIST_ELEMENT_FIELDS' => array('NAME'),
        'EDIT_ELEMENTS_EDIT_FIELDS' => ['*']
    )
);?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>