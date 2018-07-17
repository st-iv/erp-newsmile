<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<table border="1" cellspacing="0" cellpadding="2" class="treatment-invoice" data-invoice-id="<?=$arResult['INVOICE']['ID']?>">
    <tr>
        <td><?=$arResult['VISIT']['TIME_START']->format('d.m.Y H:i')?> - <?=$arResult['VISIT']['TIME_END']->format('H:i')?></td>
        <td>Пациент: <?=$arResult['VISIT']['PATIENT_NAME']?><br>Врач: <?=$arResult['VISIT']['DOCTOR_NAME']?></td>
    </tr>
    <tr>
        <td colspan="2">
            <?$APPLICATION->IncludeComponent(
                "newSmile:tooth.list",
                "",
                Array(
                )
            );?>
        </td>
    </tr>
    <tr>
        <td>
            <ul>
                <?
                $prevLevel = 1;
                foreach ($arResult['SECTION_SERVICES'] as $arSection) {
                    if ($arSection['DEPTH_LEVEL'] > $prevLevel) {
                        ?><ul><li class="section-service" data-section-id="<?=$arSection['ID']?>"><?=$arSection['NAME']?></li><?
                    } elseif ($arSection['DEPTH_LEVEL'] < $prevLevel) {
                        ?></ul><li class="section-service" data-section-id="<?=$arSection['ID']?>"><?=$arSection['NAME']?></li><?
                    } else {
                        ?><li class="section-service" data-section-id="<?=$arSection['ID']?>"><?=$arSection['NAME']?></li><?
                    }
                    $prevLevel = $arSection['DEPTH_LEVEL'];
                }
                ?>
            </ul>
        </td>
        <td id="invoice-elements-<?=$arResult['INVOICE']['ID']?>">
            <? if (isset($_REQUEST['LOAD_ELEMENTS'])) { $APPLICATION->RestartBuffer(); } ?>
            <ul>
                <?foreach ($arResult['ELEMENT_SERVICES'] as $arElement):?>
                    <li class="element-service" data-element-id="<?=$arElement['ID']?>"><?=$arElement['NAME']?>  <?=$arElement['PRICE']['PRICE']?></li>
                <?endforeach;?>
            </ul>
            <? if (isset($_REQUEST['LOAD_ELEMENTS'])) { die(); } ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <? if (isset($_REQUEST['LOAD_ITEMS'])) { $APPLICATION->RestartBuffer(); } ?>
            <table id="invoice-items-<?=$arResult['INVOICE']['ID']?>">
                <tr>
                    <th>ИД</th>
                    <th>Название</th>
                    <th>Ед. изм.</th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Сумма</th>
                </tr>
                <?foreach ($arResult['INVOICE']['ITEMS'] as $arItem):?>
                    <tr>
                        <td><?=$arItem['ID']?></td>
                        <td><?=$arResult['ELEMENTS'][$arItem['PRODUCT_ID']]['NAME']?></td>
                        <td><?=$arItem['MEASURE']?></td>
                        <td><?=$arItem['QUANTITY']?></td>
                        <td><?=$arItem['PRICE']?></td>
                        <td><?=$arItem['SUM']?></td>
                    </tr>
                <?endforeach;?>
            </table>
            <? if (isset($_REQUEST['LOAD_ITEMS'])) { die(); } ?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <button class="invoice-close">Завершить прием</button>
        </td>
    </tr>
</table>

<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
            'patient' => $arResult['PATIENT'],
        ));?>;
        window.invoiceCreate = new window.invoiceCreate(params);
    });
</script>