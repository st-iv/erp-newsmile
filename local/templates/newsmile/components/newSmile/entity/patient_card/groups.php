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
        )
    );?>
</div>

<script>
    var patientsList = new PatientsList('#patients-list');
</script>