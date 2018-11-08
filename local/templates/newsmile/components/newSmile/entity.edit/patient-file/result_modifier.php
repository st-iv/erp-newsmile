<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['FIELDS'] as &$field)
{
    if($field['TYPE'] == 'reversereference')
    {
        $defaultItem = array();

        foreach ($field['FIELDS'] as $subField)
        {
            $defaultItem[$subField['NAME']] = $subField['DEFAULT'];
        }

        $field['ITEMS'][] = $defaultItem;
    }
}

unset($field);