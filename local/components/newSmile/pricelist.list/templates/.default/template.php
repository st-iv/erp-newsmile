<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<table id="price-list" border="1">
    <tr>
        <td class="section-list">
            <ul>
                <?
                $prevLevel = 1;
                foreach ($arResult['SECTION_SERVICES_ALL'] as $arSection) {
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
        <td class="element-list">
            <? if (isset($_REQUEST['LOAD_ELEMENTS'])) { $APPLICATION->RestartBuffer(); } ?>
            <ul>
                <?foreach ($arResult['SECTION_SERVICES'] as $arElement):?>
                    <li class="section-service" data-section-id="<?=$arElement['ID']?>"><?=$arElement['NAME']?></li>
                <?endforeach;?>
                <?foreach ($arResult['ELEMENT_SERVICES'] as $arElement):?>
                    <li class="element-service" data-element-id="<?=$arElement['ID']?>"><?=$arElement['NAME']?></li>
                <?endforeach;?>
            </ul>
            <? if (isset($_REQUEST['LOAD_ELEMENTS'])) { die(); } ?>
        </td>
    </tr>
    <tr>
        <td colspan="2" id="load-content">

        </td>
    </tr>
</table>


<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
            'patient' => $arResult['PATIENT'],
        ));?>;
        window.priceList = new window.priceList(params);
    });
</script>