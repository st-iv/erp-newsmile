<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
<?$APPLICATION->IncludeComponent(
    "newSmile:doctor.edit",
    "",
    Array(
        'ID' => $arResult['VARIABLES']['ELEMENT_ID']
    )
);?>
