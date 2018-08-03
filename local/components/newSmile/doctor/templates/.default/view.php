<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
    <a href="<?=($arResult['FOLDER']. 'edit/' . $arResult['VARIABLES']['ELEMENT_ID'] . '/')?>">Редактировать</a>
<?$APPLICATION->IncludeComponent(
    "newSmile:doctor.view",
    "",
    Array(
        'ID' => $arResult['VARIABLES']['ELEMENT_ID']
    )
);?>

