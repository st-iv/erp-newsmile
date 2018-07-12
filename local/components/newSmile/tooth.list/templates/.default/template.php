<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$componentID = rand(100000, 999999);
?>
<div class="tooth-list" id="tooth-<?=$componentID?>">
    <div class="select-items select-parent-child">
        <div class="select-item select-parent active">Взрослые</div>
        <div class="select-item select-child">Детские</div>
    </div>
    <div class="select-items select-jowl">
        <div class="select-item select-top-jowl">В.Ч.</div>
        <div class="select-item select-bottom-jowl">Н.Ч.</div>
    </div>
    <div class="select-items select-tooth-items select-tooth-parent active">
        <div class="tooth-items tooth-items-top">
            <?foreach ($arResult['PARENT_TOOTH']['TOP'] as $tooth):?>
                <div class="tooth-item" data-tooth-id="<?=$tooth;?>"><?=$tooth;?></div>
            <?endforeach;?>
        </div>
        <div class="tooth-items tooth-items-bottom">
            <?foreach ($arResult['PARENT_TOOTH']['BOTTOM'] as $tooth):?>
                <div class="tooth-item" data-tooth-id="<?=$tooth;?>"><?=$tooth;?></div>
            <?endforeach;?>
        </div>
    </div>
    <div class="select-items select-tooth-items select-tooth-child" style="display: none;">
        <div class="tooth-items tooth-items-top">
            <?foreach ($arResult['CHILD_TOOTH']['TOP'] as $tooth):?>
                <div class="tooth-item" data-tooth-id="<?=$tooth;?>"><?=$tooth;?></div>
            <?endforeach;?>
        </div>
        <div class="tooth-items tooth-items-bottom">
            <?foreach ($arResult['CHILD_TOOTH']['BOTTOM'] as $tooth):?>
                <div class="tooth-item" data-tooth-id="<?=$tooth;?>"><?=$tooth;?></div>
            <?endforeach;?>
        </div>
    </div>
    <div class="select-items select-clear">Очистить</div>
</div>

<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
            'id' => 'tooth-' . $componentID,
        ));?>;
        window.toothList = new window.toothList(params);
    });
</script>