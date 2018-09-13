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
<table border=1 cellspacing=0 cellpadding=2 class="calendar-month">
    <tr class="calendar-month__nav">
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
    <tr class="calendar-month__header"><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вс</td><tr>
        <?foreach ($arResult['CALENDAR'] as $arWeek):?>
    <tr>
        <?foreach ($arWeek as $arDay):?>
            <?
            $style = '';
            if ($arResult['DATE'][$arDay] == 0){
                $style = ' style="background-color: grey;"';
            } elseif ($arResult['DATE'][$arDay] < 10) {
                $style = ' style="background-color: red;"';
            } elseif ($arResult['DATE'][$arDay] < 50) {
                $style = ' style="background-color: orange;"';
            } else {
                $style = ' style="background-color: green;"';
            }
            ?>
            <td class="calendar-month--day" data-date="<?=$arDay?>" <?=$style?>>
                <?/*?><a href="?THIS_DATE=<?=$arDay?>"><?*/?>
                    <strong><?=date('d', strtotime($arDay))?></strong>
                    <br><?=date('M', strtotime($arDay))?>
                <?/*?></a><?*/?>
            </td>
        <?endforeach;?>
    </tr>
    <?endforeach;?>
</table>

<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
        ));?>;
        new window.calendar(params);
    });
</script>