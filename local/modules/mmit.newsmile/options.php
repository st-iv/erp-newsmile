<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\String;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'mmit.newsmile');

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl("tabControl", array(
    array(
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ),
));

if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
    if (!empty($restore)) {
        Option::delete(ADMIN_MODULE_NAME);
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_RESTORED"),
            "TYPE" => "OK",
        ));
    } elseif ($request->getPost('start_time_schedule') && $request->getPost('end_time_schedule')) {
        Option::set(
            ADMIN_MODULE_NAME,
            "start_time_schedule",
            $request->getPost('start_time_schedule')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "end_time_schedule",
            $request->getPost('end_time_schedule')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "iblock_services",
            $request->getPost('iblock_services')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "id_measure_tooth",
            $request->getPost('id_measure_tooth')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "id_measure_jowl",
            $request->getPost('id_measure_jowl')
        );
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_SAVED"),
            "TYPE" => "OK",
        ));
    } else {
        CAdminMessage::showMessage(Loc::getMessage("REFERENCES_INVALID_VALUE"));
    }
}

$tabControl->begin();
?>

<form method="post" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>
    <tr>
        <td width="40%">
            <label for="start_time_schedule">Рабочии часы клиники:</label>
        <td width="60%">
            <input type="time"
                   name="start_time_schedule"
                   value="<?=String::htmlEncode(Option::get(ADMIN_MODULE_NAME, "start_time_schedule", '00:00'));?>"
                   />
             -
            <input type="time"
                   name="end_time_schedule"
                   value="<?=String::htmlEncode(Option::get(ADMIN_MODULE_NAME, "end_time_schedule", '23:59'));?>"
                   />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="iblock_services">Инфоблок Прейскурант</label>
        </td>
        <td width="60%">
            <input type="text"
                   name="iblock_services"
                   value="<?=String::htmlEncode(Option::get(ADMIN_MODULE_NAME, "iblock_services", '0'));?>">
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="id_measure_tooth">ИД ед.из. Зуб</label>
        </td>
        <td width="60%">
            <input type="text"
                   name="id_measure_tooth"
                   value="<?=String::htmlEncode(Option::get(ADMIN_MODULE_NAME, "id_measure_tooth", '0'));?>">
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="id_measure_jowl">ИД ед.из. Челюсть</label>
        </td>
        <td width="60%">
            <input type="text"
                   name="id_measure_jowl"
                   value="<?=String::htmlEncode(Option::get(ADMIN_MODULE_NAME, "id_measure_jowl", '0'));?>">
        </td>
    </tr>

    <?php
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?=Loc::getMessage("MAIN_SAVE") ?>"
           title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
           />
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
           />
    <?php
    $tabControl->end();
    ?>
</form>
