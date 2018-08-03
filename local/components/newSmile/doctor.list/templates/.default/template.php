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
    <?foreach ($arResult['DOCTORS'] as $arDoctor):?>
        <tr>
            <td><a href="/doctors/edit/<?=$arDoctor['ID']?>/"><?=$arDoctor['NAME']?></a></td>
            <td><?=$arDoctor['CLINIC_ID']?></td>
        </tr>
    <?endforeach;?>
</table>
<a href="/doctors/edit/0/">Добавить</a>