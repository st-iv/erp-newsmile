<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<br>
<br>
<table class="entity-list">
    <tr class="entity-list__header">
        <?foreach ($arResult['ELEMENT_FIELDS'] as $field):?>
            <th><?=$field['TITLE']?></th>
        <?endforeach;?>
    </tr>

    <?foreach ($arResult['ELEMENTS'] as $element):?>
        <tr class="entity-list__row" data-id="<?=$element['ID']?>">
            <?foreach ($arResult['ELEMENT_FIELDS'] as $fieldName => $field):?>
                <?$value = $element[$field['VALUE_KEY']];?>

                <td>
                    <?
                    if($field['SERIALIZED'])
                    {
                        echo implode('; ', $value);
                    }
                    else
                    {
                        switch($field['TYPE'])
                        {
                            case 'date':
                                echo $value->format('Y-m-d');
                                break;

                            case 'datetime':
                                echo $value->format('Y-m-d H:i');
                                break;

                            default:
                                echo $value;
                        }
                    }
                    ?>
                </td>
            <?endforeach;?>
        </tr>
    <?endforeach;?>
</table>


