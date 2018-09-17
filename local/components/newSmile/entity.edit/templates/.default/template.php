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

                <?switch($field['TYPE']):
                    case 'reference':?>

                        <select name="<?=$field['INPUT_NAME']?>">

                            <?if(!$field['REQUIRED']):?>
                                <option value="0">нет</option>
                            <?endif;?>

                            <?foreach ($field['REFERENCE_ITEMS'] as $item):?>
                                <option value="<?=$item['ID']?>" <?=($item['SELECTED'] ? 'selected' : '')?>>
                                    <?=$item['NAME']?>
                                </option>
                            <?endforeach;?>
                        </select>
                        <?break;?>

                    <?default:?>
                        <input type="text" name="<?=$field['INPUT_NAME']?>" value="<?=$field['VALUE']?>" <?=($field['REQUIRED'] ? 'required' : '') ?>>

                <?endswitch;?>

            </div>
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
