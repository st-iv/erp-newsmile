<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<form action="" method="POST">
    <div>
        <label>Пациент</label>
        <select name="PATIENT_ID" id="">
            <?foreach ($arResult['PATIENT'] as $arPatient):?>
                <option value="<?=$arPatient['ID'];?>"><?=$arPatient['NAME'];?></option>
            <?endforeach;?>
        </select>
    </div>
    <div>
        <label>Врач</label>
        <select name="DOCTOR_ID" id="">
            <?foreach ($arResult['DOCTOR'] as $arDoctor):?>
                <option value="<?=$arDoctor['ID'];?>"><?=$arDoctor['NAME'];?></option>
            <?endforeach;?>
        </select>
    </div>
    <div>
        <label>Длительность</label>
        <input type="number" name="DURATION">
    </div>
    <div>
        <label>Описание</label>
        <textarea name="DESCRIPTION"></textarea>
    </div>
    <div>
        <input type="hidden" name="DATE">
        <table class="calendar" border=1 cellspacing=0 cellpadding=2>
            <tr><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вс</td><tr>
                <?foreach ($arResult['CALENDAR'] as $arWeek):?>
            <tr class="items-calendar">
                <?foreach ($arWeek as $arDay):?>
                    <td data-date="<?=$arDay;?>">
                        <a>
                            <strong><?=date('d', strtotime($arDay))?></strong>
                            <br><?=date('M', strtotime($arDay))?>
                        </a>
                    </td>
                <?endforeach;?>
            </tr>
            <?endforeach;?>
        </table>
    </div>
    <button type="submit">Добавить</button>
</form>