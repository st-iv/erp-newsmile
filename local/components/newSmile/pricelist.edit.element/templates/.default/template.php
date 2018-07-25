<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<form action="/price-list/element.php" method="post">
    <input type="hidden" name="ID" value="<?=$arResult['ITEM']['ID'];?>">
    <input type="hidden" name="SECTION_ID" value="<?=($arResult['ITEM']['IBLOCK_SECTION_ID'])?$arResult['ITEM']['IBLOCK_SECTION_ID']:$_REQUEST['SECTION_ID'];?>">
    <table id="price-list" border="1">
        <tr>
            <td>Название</td>
            <td><input type="text" name="NAME" value="<?=$arResult['ITEM']['NAME'];?>"></td>
        </tr>
        <tr>
            <td>Единица измерения</td>
            <td>
                <select name="MEASURE">
                    <?foreach ($arResult['MEASURE'] as $arMeasure):?>
                        <? $isSelected = $arMeasure['ID'] == $arResult['ITEM']['MEASURE']; ?>
                        <option value="<?=$arMeasure['ID']?>" <?=($isSelected)?'selected':'';?>><?=$arMeasure['NAME']?></option>
                    <?endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Цена</td>
            <td><input type="text" name="PRICE" value="<?=$arResult['ITEM']['PRICE'];?>"></td>
        </tr>
        <tr>
            <td colspan="2">План лечения</td>
        </tr>
        <tr>
            <td>Минимальная цена</td>
            <td><input type="text" name="MIN_PRICE" value="<?=$arResult['ITEM']['PROPERTY_MINIMUM_PRICE_VALUE'];?>"></td>
        </tr>
        <tr>
            <td>Максимальная цена</td>
            <td><input type="text" name="MAX_PRICE" value="<?=$arResult['ITEM']['PROPERTY_MAXIMUM_PRICE_VALUE'];?>"></td>
        </tr>
    </table>
    <button type="submit" name="action" value="edit">Сохранить</button>
</form>
