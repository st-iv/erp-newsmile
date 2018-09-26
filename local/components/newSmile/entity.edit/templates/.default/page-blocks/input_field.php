<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?switch($outputField['TYPE']):
    case 'enum':
    case 'reference':?>

        <select name="<?=$outputField['INPUT_NAME']?>" <?=($outputField['DISABLED'] ? 'disabled' : '')?>>

            <?if(!$outputField['REQUIRED']):?>
                <option value="0">нет</option>
            <?endif;?>

            <?foreach ($outputField['VARIANTS'] as $itemValue => $item):?>
                <option value="<?=$itemValue?>" <?=($item['SELECTED'] ? 'selected' : '')?>>
                    <?=$item['NAME']?>
                </option>
            <?endforeach;?>
        </select>
        <?break;?>

    <?case 'boolean':?>
        <input type="checkbox" name="<?=$outputField['INPUT_NAME']?>"
               value="<?=$outputField['TRUE_VALUE']?>" <?=($outputField['CHECKED'] ? 'checked' : '')?> <?=($outputField['DISABLED'] ? 'disabled' : '')?>>
        <?break;?>

    <?case 'datetime':?>
        <?
        /**
         * @var \Bitrix\Main\Type\DateTime $value
         */
        $value = $outputField['VALUE'];
        ?>
        <input type="datetime-local" name="<?=$outputField['INPUT_NAME']?>" value="<?=$value->format('Y-m-d\TH:i:s')?>" <?=($outputField['DISABLED'] ? 'disabled' : '')?>>
        <?break;?>

    <?case 'hidden':?>
        <input type="hidden" name="<?=$outputField['INPUT_NAME']?>" value="<?=$outputField['VALUE']?>" <?=($outputField['DISABLED'] ? 'disabled' : '')?>>
        <?break;?>

    <?default:?>
        <input type="text" name="<?=$outputField['INPUT_NAME']?>" value="<?=$outputField['VALUE']?>" <?=($outputField['REQUIRED'] ? 'required' : '') ?> <?=($outputField['DISABLED'] ? 'disabled' : '')?>>

<?endswitch;?>
