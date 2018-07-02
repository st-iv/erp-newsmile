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
        <th>Дата добавления</th>
        <th>Пациент</th>
        <th>Врач</th>
        <th>Длительность</th>
        <th>Предпочтительные дни</th>
    </tr>
    <?foreach ($arResult['ITEMS'] as $arItem):?>
        <tr>
            <td><?=$arItem['TIMESTAMP_X']->format('d.m.Y H:i');?></td>
            <td><?=$arItem['UF_PATIENT_NAME']?></td>
            <td><?=$arItem['UF_DOCTOR_NAME']?></td>
            <td><?=$arItem['DURATION']?></td>
            <td>
                <table>
                    <tr>
                        <?$arDate = json_decode($arItem['DATE']);
                        foreach ($arDate as $date):?>
                            <td>
                                <strong><?=date('d', strtotime($date))?></strong>
                                <br><?=date('M', strtotime($date))?>
                            </td>
                        <?endforeach;?>
                    </tr>
                </table>
            </td>
        </tr>
    <?endforeach;?>
</table>
