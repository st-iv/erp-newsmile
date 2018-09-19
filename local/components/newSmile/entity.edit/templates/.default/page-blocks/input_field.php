<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?switch($field['TYPE']):
    case 'reference':?>

        <select name="<?=$field['INPUT_NAME']?>" <?=($field['DISABLED'] ? 'disabled' : '')?>>

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

    <?case 'boolean':?>
        <input type="checkbox" name="<?=$field['INPUT_NAME']?>"
               value="<?=$field['TRUE_VALUE']?>" <?=($field['CHECKED'] ? 'checked' : '')?> <?=($field['DISABLED'] ? 'disabled' : '')?>>
        <?break;?>

    <?case 'datetime':?>
        <?
        /**
         * @var \Bitrix\Main\Type\DateTime $value
         */
        $value = $field['VALUE'];
        ?>
        <input type="datetime-local" name="<?=$field['INPUT_NAME']?>" value="<?=$value->format('Y-m-d\TH:i:s')?>" <?=($field['DISABLED'] ? 'disabled' : '')?>>
        <?break;?>

    <?case 'hidden':?>
        <input type="hidden" name="<?=$field['INPUT_NAME']?>" value="<?=$field['VALUE']?>" <?=($field['DISABLED'] ? 'disabled' : '')?>>
        <?break;?>

    <?default:?>
        <input type="text" name="<?=$field['INPUT_NAME']?>" value="<?=$field['VALUE']?>" <?=($field['REQUIRED'] ? 'required' : '') ?> <?=($field['DISABLED'] ? 'disabled' : '')?>>

<?endswitch;?>
