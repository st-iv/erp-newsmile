<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>

<?
if (isset($_GET['date'])) echo "выбрана дата ".$_GET['date'];
?>
<table border=1 cellspacing=0 cellpadding=2>
    <tr>
        <td colspan=7>
            <table width="100%" border=0 cellspacing=0 cellpadding=0>
                <tr>
                    <td align="left"><?=$arResult['LINK_PREV']?></td>
                    <td align="center"></td>
                    <td align="right"><?=$arResult['LINK_NEXT']?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вс</td><tr>
        <?foreach ($arResult['CALENDAR'] as $arWeek):?>
    <tr>
        <?foreach ($arWeek as $arDay):?>
            <?
            $style = '';
            if ($arResult['DATE'][$arDay]){
                $style = ' style="background-color: red;"';
            }
            ?>
            <td <?=$style?>>
                <a href="?THIS_DATE=<?=$arDay?>">
                    <strong><?=date('d', strtotime($arDay))?></strong>
                    <br><?=date('M', strtotime($arDay))?>
                </a>
            </td>
        <?endforeach;?>
    </tr>
    <?endforeach;?>
</table>