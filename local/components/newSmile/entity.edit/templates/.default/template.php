<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<form action="<?=POST_FORM_ACTION_URI?>" class="entity-edit-form">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="ajax" value="Y">
    <input type="hidden" name="action" value="<?=$arResult['ACTION']?>">

    <?foreach($arResult['FIELDS'] as $field):?>

        <?$outputField = $field;?>

        <?if($field['TYPE'] == 'hidden'):?>
            <?include __DIR__ . '/page-blocks/input_field.php'?>
            <?continue;?>
        <?endif;?>

        <div class="edit-form__row">

            <?if($field['TYPE'] != 'reversereference'):?>

                <div class="field__title">
                    <?=$field['TITLE']?>
                </div>

                <div class="field__input">
                    <?include __DIR__ . '/page-blocks/input_field.php'?>
                </div>

            <?else:?>
                <div class="edit-form__row edit-form__row--table">

                    <b><?=$field['TITLE']?></b>

                    <table class="reverse-reference">
                        <tr>
                            <?foreach ($field['FIELDS'] as $subField):?>
                                <?if($subField['TYPE'] == 'hidden') continue;?>
                                <th><?=$subField['TITLE']?></th>
                            <?endforeach;?>
                        </tr>

                        <?foreach ($field['ITEMS'] as $item):?>
                            <?$isTemplate = empty($item['ID']);?>
                            <tr class="<?=($isTemplate ? 'template-field js-template-field' : '')?>">
                                <?foreach ($item as $fieldName => $fieldValue):?>
                                    <?
                                    $outputField = $field['FIELDS'][$fieldName];
                                    $outputField['VALUE'] = $fieldValue;

                                    $outputField['DISABLED'] = $isTemplate;


                                    if($outputField['TYPE'] == 'hidden'):?>
                                        <?include __DIR__ . '/page-blocks/input_field.php'?>
                                    <?else:?>
                                        <?
                                        if($outputField['TYPE'] == 'reference')
                                        {
                                            $outputField['VARIANTS'][$fieldValue]['SELECTED'] = true;
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
            <?endif;?>
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

