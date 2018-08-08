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
<div class="dental-formula">
    <div class="dental-formula--top">
        <div class="dental-formula__items dental-formula--top--left">
            <?foreach ($arResult['PARENT_TOOTH']['TOP_LEFT'] as $key => $tooth):?>
                <div class="dental-formula__item">
                    <div class="dental-formula__item--parent">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['PARENT_TOOTH']['TOP_LEFT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['PARENT_TOOTH']['TOP_LEFT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item--child">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['CHILD_TOOTH']['TOP_LEFT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['CHILD_TOOTH']['TOP_LEFT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item__checked">
                        <input type="checkbox" checked>
                    </div>
                </div>
            <?endforeach;?>
        </div>
        <div class="dental-formula__items dental-formula--top--right">
            <?foreach ($arResult['PARENT_TOOTH']['TOP_RIGHT'] as $key => $tooth):?>
                <div class="dental-formula__item">
                    <div class="dental-formula__item--parent">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['PARENT_TOOTH']['TOP_RIGHT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['PARENT_TOOTH']['TOP_RIGHT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item--child">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['CHILD_TOOTH']['TOP_RIGHT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['CHILD_TOOTH']['TOP_RIGHT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item__checked">
                        <input type="checkbox" checked>
                    </div>
                </div>
            <?endforeach;?>
        </div>
    </div>
    <div class="dental-formula--bottom">
        <div class="dental-formula__items dental-formula--bottom--left">
            <?foreach ($arResult['PARENT_TOOTH']['BOTTOM_LEFT'] as $key => $tooth):?>
                <div class="dental-formula__item">
                    <div class="dental-formula__item--parent">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['PARENT_TOOTH']['BOTTOM_LEFT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['PARENT_TOOTH']['BOTTOM_LEFT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item--child">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['CHILD_TOOTH']['BOTTOM_LEFT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['CHILD_TOOTH']['BOTTOM_LEFT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item__checked">
                        <input type="checkbox" checked>
                    </div>
                </div>
            <?endforeach;?>
        </div>
        <div class="dental-formula__items dental-formula--bottom--right">
            <?foreach ($arResult['PARENT_TOOTH']['BOTTOM_RIGHT'] as $key => $tooth):?>
                <div class="dental-formula__item">
                    <div class="dental-formula__item--parent">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['PARENT_TOOTH']['BOTTOM_RIGHT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['PARENT_TOOTH']['BOTTOM_RIGHT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item--child">
                        <div class="dental-formula__item__tooth" data-number="<?=$arResult['CHILD_TOOTH']['BOTTOM_RIGHT'][$key]?>"></div>
                        <div class="dental-formula__item__number"><?=$arResult['CHILD_TOOTH']['BOTTOM_RIGHT'][$key]?></div>
                    </div>
                    <div class="dental-formula__item__checked">
                        <input type="checkbox" checked>
                    </div>
                </div>
            <?endforeach;?>
        </div>
    </div>
</div>
<button id="json-formula">Готово</button>

<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
            'status' => $arResult['STATUS'],
        ));?>;
        window.dentalFormila = new window.dentalFormila(params);
    });
</script>