<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<table>
    <tr>
        <td>Статус</td>
        <td><?=$arResult['PATIENT_CARD']['STATUS_NAME']?></td>
    </tr>
    <tr>
        <td>Общая информация</td>
    </tr>
    <tr>
        <td>ФИО</td>
        <td><?=$arResult['PATIENT_CARD']['USER_LAST_NAME']?> <?=$arResult['PATIENT_CARD']['USER_NAME']?> <?=$arResult['PATIENT_CARD']['USER_SECOND_NAME']?></td>
    </tr>
    <tr>
        <td>Дата рождения</td>
        <td><?=$arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY']?></td>
    </tr>
    <tr>
        <td>Адрес</td>
        <td><?=$arResult['PATIENT_CARD']['USER_PERSONAL_ZIP']?> <?=$arResult['PATIENT_CARD']['USER_PERSONAL_CITY']?> <?=$arResult['PATIENT_CARD']['USER_PERSONAL_STREET']?> (<?=$arResult['PATIENT_CARD']['USER_PERSONAL_NOTES']?>)</td>
    </tr>
    <tr>
        <td>Контакты</td>
        <td><?=$arResult['PATIENT_CARD']['USER_PERSONAL_PHONE']?>, <?=$arResult['PATIENT_CARD']['USER_PERSONAL_MOBILE']?></td>
    </tr>
    <tr>
        <td>Лечения</td>
    </tr>
    <tr>
        <td>Перый прием</td>
        <td><?=$arResult['PATIENT_CARD']['FIRST_VISIT']?></td>
    </tr>
    <tr>
        <td>Последний прием</td>
        <td><?=$arResult['PATIENT_CARD']['LAST_VISIT']?></td>
    </tr>
    <tr>
        <td>Лечащие врачи</td>
        <td><?=implode(',', $arResult['PATIENT_CARD']['DOCTORS_NAME'])?></td>
    </tr>
    <tr>
        <td>Документы</td>
    </tr>
    <tr>
        <td>Паспорт</td>
        <td><?=$arResult['PATIENT_CARD']['PASSPORT_SN']?><br>
            <?=$arResult['PATIENT_CARD']['PASSPORT_ISSUED_BY']?><br>
            <?=$arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']?><br>
            <?=$arResult['PATIENT_CARD']['PASSPORT_PLACE_BIRTH']?><br>
            <?=$arResult['PATIENT_CARD']['PASSPORT_ADDRESS']?><br>
            <?=$arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']?><br>
            <?=$arResult['PATIENT_CARD']['PASSPORT_OTHER']?></td>
    </tr>
    <tr>
        <td>Полисы</td>
        <td></td>
    </tr>
    <tr>
        <td>Дополнительная информация</td>
    </tr>
    <tr>
        <td>Источник</td>
        <td><?=$arResult['PATIENT_CARD']['SOURCE']?></td>
    </tr>
    <tr>
        <td>Скидки</td>
        <td></td>
    </tr>
    <tr>
        <td>Причина списания в архив</td>
        <td><?=$arResult['PATIENT_CARD']['ARCHIVE']?></td>
    </tr>
</table>