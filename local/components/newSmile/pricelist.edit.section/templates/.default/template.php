<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<form action="/price-list/section.php" method="post">
    <input type="hidden" name="ID" value="<?=$arResult['ITEM']['ID'];?>">
    <input type="hidden" name="SECTION_ID" value="<?=($arResult['ITEM']['IBLOCK_SECTION_ID'])?$arResult['ITEM']['IBLOCK_SECTION_ID']:$_REQUEST['SECTION_ID'];?>">
    <table id="price-list" border="1">
        <tr>
            <td>Название</td>
            <td><input type="text" name="NAME" value="<?=$arResult['ITEM']['NAME'];?>"></td>
        </tr>
    </table>
    <button type="submit" name="action" value="edit">Сохранить</button>
</form>
