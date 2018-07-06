<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
<?$APPLICATION->IncludeComponent(
    "newSmile:patient.card.edit",
    "",
    Array(
        'ID' => $arResult['VARIABLES']['ELEMENT_ID']
    )
);?>
