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
        <label>Название</label>
        <input type="text" name="NAME">
    </div>
    <div>
        <label>Дата</label>
        <input type="date" name="DATA">
    </div>
    <div>
        <label>Время начала</label>
        <input type="time" name="TIME_START">
    </div>
    <div>
        <label>Время окончания</label>
        <input type="time" name="TIME_END">
    </div>
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
        <label>Кресло</label>
        <select name="WORK_CHAIR_ID" id="">
            <?foreach ($arResult['WORK_CHAIR'] as $arWorkChair):?>
                <option value="<?=$arWorkChair['ID'];?>"><?=$arWorkChair['NAME'];?></option>
            <?endforeach;?>
        </select>
    </div>
    <button type="submit">Добавить</button>
</form>