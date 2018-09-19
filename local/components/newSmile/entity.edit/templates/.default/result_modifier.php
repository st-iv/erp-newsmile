<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['REVERSE_REFERENCES'] as &$reverseReference)
{
    $defaultItem = array();

    foreach ($reverseReference['FIELDS'] as $field)
    {
        $defaultItem[$field['NAME']] = $field['DEFAULT'];
    }

    $reverseReference['ITEMS'][] = $defaultItem;
}

unset($reverseReference);