<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?if($arResult['SECTIONS']):?>
    Группы:
    <ul class="groups-list">
        <?foreach ($arResult['SECTIONS'] as $arSection):?>
            <li class="section-service" data-section-url="<?=$arSection['URL']?>" data-edit-url="<?=$arSection['EDIT_URL']?>">
                <?=$arSection['NAME']?>
            </li>
        <?endforeach;?>
    </ul>
<?endif;?>
<?if($arResult['ELEMENTS']):?>
    Материалы:
    <ul class="elements-list">
        <?foreach ($arResult['ELEMENTS'] as $arElement):?>
            <li class="element-service" data-edit-url="<?=$arElement['EDIT_URL']?>">
                <?=$arElement['NAME']?>
            </li>
        <?endforeach;?>
    </ul>
<?endif;?>

<?if(!$arResult['SECTIONS'] && !$arResult['ELEMENTS']):?>Пусто :(<?endif;?>