<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<script>
    BX.ready(function()
    {
        window.entitiesList = new window.entitiesList({
            'ajaxUrlAddGroup': '<?=$arResult['FOLDER'].$arResult['URL_TEMPLATES']['add_group']?>',
            'ajaxUrlAddElement': '<?=$arResult['FOLDER'].$arResult['URL_TEMPLATES']['add_element']?>',
            'sessid': '<?=bitrix_sessid()?>'
        });
    });
</script>
