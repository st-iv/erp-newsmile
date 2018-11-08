<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if($data['FIELD']['SERIALIZED'] && is_array($data['VALUE']))
{
    echo implode(',', $data['VALUE']);
}
else
{
    switch($data['FIELD']['TYPE'])
    {
        case 'datetime':
            echo $data['VALUE']->format('d.m.Y H:i:s');
            break;

        case 'date':
            echo $data['VALUE']->format('d.m.Y');
            break;

        case 'enum':
            echo $data['FIELD']['VARIANTS'][$data['VALUE']];
            break;

        default:
            echo $data['VALUE'];
    }
}