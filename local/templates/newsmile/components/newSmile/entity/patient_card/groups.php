<div class="patients-list" id="patients-list">
    <?
    $APPLICATION->IncludeComponent(
        "newSmile:entity.list",
        "just_table",
        Array(
            'DATA_MANAGER_CLASS_ELEMENT' => '\\Mmit\\NewSmile\\PatientCardTable',
            'ELEMENT_FIELDS' => ['LAST_NAME', 'NAME', 'SECOND_NAME', 'PERSONAL_PHONE', 'PERSONAL_BIRTHDAY'],
            'ELEMENT_NAME_TEMPLATE' => '#LAST_NAME# #NAME# #SECOND_NAME#',
            'ELEMENT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['element'],
            /*'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
            'GROUP_FIELDS' => $arParams['LIST_SECTION_FIELDS'],
            'ELEMENT_FIELDS' => $arParams['LIST_ELEMENT_FIELDS'],
            'INDEX_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['groups'],
            'SECTION_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['group'],
            'ELEMENT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['element'],
            'SECTION_EDIT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['edit_group'],
            'ELEMENT_EDIT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['edit_element'],*/
        )
    );?>
</div>

<script>
    var patientsList = new PatientsList('#patients-list');
</script>