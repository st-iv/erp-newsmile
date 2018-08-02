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
        <th>ФИО</th>
        <th>Клиника</th>
    </tr>
    <?foreach ($arResult['WORK_CHAIRS'] as $arWorkChair):?>
        <tr>
            <td><a href="/work-chair/<?=$arWorkChair['ID']?>/"><?=$arWorkChair['NAME']?></a></td>
            <td><?=$arWorkChair['CLINIC_ID']?></td>
        </tr>
    <?endforeach;?>
    <tr>
        <td><a href="/work-chair/0/">Добавить</a></td>
    </tr>
</table>