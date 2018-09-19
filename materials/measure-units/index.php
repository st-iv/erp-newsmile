<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Единицы измерения");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "elements_only",
    Array(
        'ENTITY_CLASS_ELEMENT' => 'Mmit\NewSmile\MeasureUnitTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/materials/measure-units/',
        'LIST_ELEMENT_FIELDS' => array('NAME'),
    )
);?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>