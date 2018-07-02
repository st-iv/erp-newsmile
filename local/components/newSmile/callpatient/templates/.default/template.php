<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<table border="1">
    <tr>
        <th>Пациент</th>
        <th>Прием</th>
    </tr>
    <?foreach ($arResult['VISIT'] as $arVisit):?>
        <tr>
            <td>
                <?=$arVisit['UF_PATIENT_NAME'];?>
            </td>
            <td>
                <?=$arVisit['DATE_START']->format('d.m.Y');?> | <?=$arVisit['TIME_START']->format('H:i');?> - <?=$arVisit['TIME_END']->format('H:i');?>
            </td>
        </tr>
    <?endforeach;?>
</table>