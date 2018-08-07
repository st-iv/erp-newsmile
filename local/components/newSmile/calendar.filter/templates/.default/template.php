<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
use Bitrix\Main\Config\Option;
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arFilter = $arResult['CURRENT_FILTER'];

?>
<form action="" method="post">
    <table>
        <tr>
            <td>
                <select name="TIME_FROM" id="">
                    <option value="">--:--</option>
                    <?foreach ($arResult['FILTER']['FILTER_TIME'] as $filter):?>
                        <option value="<?=$filter?>" <?=($arFilter['TIME_FROM'] == $filter)?'selected':'';?>><?=$filter?></option>
                    <?endforeach;?>
                </select>
                <select name="TIME_TO" id="">
                    <option value="">--:--</option>
                    <?foreach ($arResult['FILTER']['FILTER_TIME'] as $filter):?>
                        <option value="<?=$filter?>" <?=($arFilter['TIME_TO'] == $filter)?'selected':'';?>><?=$filter?></option>
                    <?endforeach;?>
                </select>
            </td>
            <td>
                <select name="DOCTOR" id="">
                    <option value="">не выбран</option>
                    <?foreach ($arResult['DOCTORS'] as $arDoctor):?>
                        <option value="<?=$arDoctor['ID']?>" <?=($arFilter['DOCTOR'] == $arDoctor['ID'])?'selected':'';?>><?=$arDoctor['NAME']?></option>
                    <?endforeach;?>
                </select>
            </td>
            <td>
                <button type="submit">Применить</button>
            </td>
        </tr>
    </table>
</form>