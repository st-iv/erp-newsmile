<?php
global $USER;
$rsUser = \Bitrix\Main\UserTable::getList([
    'filter' => [
        'ID' => $USER->GetID()
    ],
    'select' => [
        'UF_CLINIC'
    ]
]);
if ($arUser = $rsUser->fetch()) {
    session_start();
    $_SESSION['CLINIC_ID'] = $arUser['UF_CLINIC'];
}