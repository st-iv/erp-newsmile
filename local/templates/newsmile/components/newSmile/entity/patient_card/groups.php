<div class="patients-list">
    <?
    $APPLICATION->IncludeComponent(
        "newSmile:entity.list",
        "just_table",
        Array(
            'DATA_MANAGER_CLASS_ELEMENT' => '\\Mmit\\NewSmile\\PatientCardTable',
            'ELEMENT_FIELDS' => ['LAST_NAME', 'NAME', 'SECOND_NAME', 'PERSONAL_PHONE', 'PERSONAL_BIRTHDAY'],
            'ELEMENT_NAME_TEMPLATE' => '#LAST_NAME# #NAME# #SECOND_NAME#',
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
    $(document).ready(function()
    {
        $('.patients-list tr.entity-list__row').each(function()
        {
            $(this).magnificPopup({
                type: 'ajax',
                ajax: {
                    settings: {
                        url: '/ajax/patientcard/',
                        method: 'post',

                    }
                }
            });
        });

        $('.patients-list tr td').magnificPopup({
            items: {
                src: ''
            }
        });
    });
</script>