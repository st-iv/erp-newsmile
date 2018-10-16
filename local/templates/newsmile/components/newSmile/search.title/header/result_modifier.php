<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['CATEGORIES'] as $categoryCode => &$category)
{
    $bUniteSubcategories = ($categoryCode == 'doctor');

    foreach ($category['SUBCATEGORIES'] as $subcategoryCode => &$items)
    {
        foreach ($items as &$item)
        {
            $item['SEARCH_ENTRY'] = str_replace(array('<b>', '</b>'), array('<span>', '</span>'), $item['SEARCH_ENTRY']);
            $item['AGE'] = \Mmit\NewSmile\Date\Helper::getAge($item['PERSONAL_BIRTHDAY']);
            $item['FIO'] = \Mmit\NewSmile\Helpers::getFio($item);

            // объединение подкатегорий в подкатегорию MAIN для врачей
            if($bUniteSubcategories && ($subcategoryCode != 'MAIN'))
            {
                if(!$category['SUBCATEGORIES']['MAIN'][$item['ID']])
                {
                    $category['SUBCATEGORIES']['MAIN'][$item['ID']] = $item;
                    unset($category['SUBCATEGORIES']['MAIN'][$item['ID']]['SEARCH_ENTRY']);
                }

                $category['SUBCATEGORIES']['MAIN'][$item['ID']]['ENTRIES'][$subcategoryCode] = $item['SEARCH_ENTRY'];
            }
        }

        unset($item);
    }

    unset($items);

    if($bUniteSubcategories)
    {
        $category['SUBCATEGORIES'] = array(
            'MAIN' => $category['SUBCATEGORIES']['MAIN']
        );
    }
}

unset($category);