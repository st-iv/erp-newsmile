<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
    <a href="<?=($arResult['FOLDER']. 'edit/' . $arResult['VARIABLES']['ELEMENT_ID'] . '/')?>">Редактировать</a>
<?$APPLICATION->IncludeComponent(
    "newSmile:patient.card.view",
    "",
    Array(
        'ID' => $arResult['VARIABLES']['ELEMENT_ID']
    )
);?>

<?$APPLICATION->IncludeComponent(
    "newSmile:patient.card.treatmentplan",
    "",
    Array(
        'PATIENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
    )
);?>


<table border="1" width="100%">
    <tr>
        <td>Изображения</td>
    </tr>
    <tr>
        <td>
            <?$APPLICATION->IncludeComponent(
                    'newSmile:patient.card.file',
                    '',
                    array(
                            'ID' => $arResult['VARIABLES']['ELEMENT_ID']
                    )
                )?>
        </td>
    </tr>
</table>