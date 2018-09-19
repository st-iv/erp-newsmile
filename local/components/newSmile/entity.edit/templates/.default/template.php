<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<form action="<?=POST_FORM_ACTION_URI?>" class="entity-edit-form">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="ajax" value="Y">
    <input type="hidden" name="action" value="<?=$arResult['ACTION']?>">

    <?foreach($arResult['FIELDS'] as $field):?>
        <div class="edit-form__row">
            <div class="field__title">
                <?=$field['TITLE']?>
            </div>

            <div class="field__input">
                <?include __DIR__ . '/page-blocks/input_field.php'?>
            </div>
        </div>
    <?endforeach;?>

    <?foreach($arResult['REVERSE_REFERENCES'] as $reverseReference):?>

        <div class="edit-form__row edit-form__row--table">

            <b><?=$reverseReference['TITLE']?></b>

            <table class="reverse-reference">
                <tr>
                    <?foreach ($reverseReference['FIELDS'] as $field):?>
                        <th><?=$field['TITLE']?></th>
                    <?endforeach;?>
                </tr>

                <?foreach ($reverseReference['ITEMS'] as $item):?>
                    <?$isTemplate = empty($item['ID']);?>
                    <tr class="<?=($isTemplate ? 'template-field js-template-field' : '')?>">
                        <?foreach ($item as $fieldName => $fieldValue):?>
                            <?
                            $field = $reverseReference['FIELDS'][$fieldName];
                            $field['VALUE'] = $fieldValue;

                            $field['DISABLED'] = $isTemplate;

                            if($field['TYPE'] == 'hidden'):?>
                                <?include __DIR__ . '/page-blocks/input_field.php'?>
                            <?else:?>
                                <?
                                if($field['TYPE'] == 'reference')
                                {
                                    $field['REFERENCE_ITEMS'][$fieldValue]['SELECTED'] = true;
                                }
                                ?>
                                <td>
                                    <?include __DIR__ . '/page-blocks/input_field.php'?>
                                </td>
                            <?endif;?>
                        <?endforeach;?>
                    </tr>
                <?endforeach;?>

            </table>

        </div>
    <?endforeach;?>

    <div class="edit-form__row">
        <button type="submit">
            <?if($arResult['ACTION'] == 'add'):?>
                Добавить
            <?else:?>
                Сохранить
            <?endif;?>
        </button>
    </div>
</form>

