<?

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Loader;

$module_id = 'mmit.newsmile';

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
Loc::loadMessages(__FILE__);

Loader::includeModule($module_id);

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

$arTabs = \Mmit\NewSmile\Config::getOptionsPageConfig();


// сохранение
if($request->isPost() && $request['Update'] && check_bitrix_sessid())
{
    foreach($arTabs as $arTab)
    {
        foreach($arTab['OPTIONS'] as $arOption)
        {
            if(is_array($arOption) && !$arOption['note'])
            {
                $optionName = $arOption[0];
                $optionValue = $request->getPost($optionName);
                Option::set($module_id, $optionName, $optionValue);
            }
        }

    }
}

?>

<?
// отображение
$tabControl = new CAdminTabControl('tabControl', $arTabs);
?>

<?$tabControl->Begin();?>
<form method="post"
      action="<?=$APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>"
      name="mmit_newsmile_settings">

    <?
    foreach($arTabs as $arTab)
    {
        $tabControl->BeginNextTab();
        __AdmSettingsDrawList($module_id, $arTab['OPTIONS']);

    }

    $tabControl->BeginNextTab();
    $tabControl->Buttons();
    ?>


    <input type="submit" name="Update" value="<?=Loc::getMessage('MAIN_SAVE')?>">
    <input type="reset" name="reset" value="<?=Loc::getMessage('MAIN_RESET')?>">

    <?=bitrix_sessid_post()?>

</form>

<?$tabControl->End();?>

