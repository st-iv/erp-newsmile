<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
    <table id="entities-list" border="1" class="elements-list-table">
        <tr>
            <td class="element-list">
                <ul class="elements-list">
                    <?if(!$arResult['ELEMENTS']):?>Пусто :(<?endif;?>

                    <?foreach ($arResult['ELEMENTS'] as $arElement):?>
                        <li class="element-service" data-edit-url="<?=$arElement['EDIT_URL']?>">
                            <?=$arElement['NAME_BY_TEMPLATE']?>
                        </li>
                    <?endforeach;?>
                </ul>
            </td>
        </tr>
        <tr>
            <td id="load-content">

            </td>
        </tr>
    </table>
