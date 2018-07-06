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
    </tr>
    <?foreach ($arResult['PATIENT_CARDS'] as $arPatientCard):?>
        <tr>
            <td><a href="/patientcard/view/<?=$arPatientCard['ID']?>/"><?=$arPatientCard['NAME']?></a></td>
        </tr>
    <?endforeach;?>
</table>