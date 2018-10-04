<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile;
use Bitrix\Main\Loader;

class NewSmileSearchTitleComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        if(!$arParams['USE_LANGUAGE_GUESS'])
        {
            $arParams['USE_LANGUAGE_GUESS'] = 'Y';
        }

        $arParams['MIN_QUERY_LENGTH'] = (int)$arParams['MIN_QUERY_LENGTH'];

        if($arParams['MIN_QUERY_LENGTH'] <= 0)
        {
            $arParams['MIN_QUERY_LENGTH'] = 3;
        }

        $arParams['TOP_COUNT'] = (int)$arParams['TOP_COUNT'];

        if($arParams['TOP_COUNT'] <= 0)
        {
            $arParams['TOP_COUNT'] = 5;
        }

        return $arParams;
    }

    protected function processRequest(\Bitrix\Main\HttpRequest $request)
    {
        $query = ltrim($request['q']);

        if(empty($query) || !Loader::includeModule('search')) return;

        CUtil::decodeURIComponent($query);

        $query = urldecode($query);

        $this->arResult['ALT_QUERY'] = '';
        if($this->arParams['USE_LANGUAGE_GUESS'] !== 'N')
        {
            $arLang = CSearchLanguage::GuessLanguage($query);
            if(is_array($arLang) && $arLang["from"] != $arLang["to"])
            {
                $this->arResult['ALT_QUERY'] = CSearchLanguage::ConvertKeyboardLayout($query, $arLang["from"], $arLang["to"]);
            }
        }

        $this->arResult['QUERY'] = $query;
        $this->arResult['PHRASE'] = stemming_split($query, LANGUAGE_ID);

        $query = preg_replace(
            '/(\S+)/',
            '"\\1"',
            ($this->arResult['ALT_QUERY']  ? $this->arResult['ALT_QUERY'] : $query)
        );


        $this->arResult['CATEGORIES'] = array();

        $obSearch = new CSearch();
        $searchExFilter = array();


        foreach ($this->arParams['CATEGORIES'] as $categoryCode => $category)
        {
            $searchExFilter['PARAM1'][] = $categoryCode;
        }

        $obSearch->Search(
            array(
                'QUERY' => $query,
                'MODULE_ID' => 'mmit.newsmile',
            ),
            array(),
            $searchExFilter
        );

        if(!$obSearch->error)
        {
            $queryElementsInfo = $this->writeSearchResults($obSearch);
            $this->queryAdditionalInfo($queryElementsInfo);
            $this->sortCategories();
        }
    }

    /**
     * Пишет результаты поиска в arResult, подготавливает данные для запроса дополнительной информации
     *
     * @param CSearch $search
     *
     * @return array данные для запроса дополнительной информации
     */
    protected function writeSearchResults(CSearch $search)
    {
        $queryElementsInfo = array();

        while($searchResult = $search->Fetch())
        {
            $categoryCode = $searchResult['PARAM1'];
            $category =& $this->arResult['CATEGORIES'][$categoryCode];
            $categoryParams = $this->arParams['CATEGORIES'][$categoryCode];

            $category['TITLE'] = $categoryParams['TITLE'];
            $category['CODE'] = $categoryCode;

            if(preg_match('/^' . $categoryCode . '_([a-z_]+)_' . $searchResult['PARAM2'] .'$/', $searchResult['ITEM_ID'], $matches))
            {
                $subCategoryName = strtoupper($matches[1]);
            }
            else
            {
                $subCategoryName = 'MAIN';
            }
            $category['SUBCATEGORIES'][$subCategoryName][$searchResult['PARAM2']] = array(
                'ID' => $searchResult['PARAM2'],
                'SEARCH_ENTRY' => $searchResult['TITLE_FORMATED']
            );

            if($categoryParams['ENTITY'] && $categoryParams['FIELDS'])
            {
                $queryElementsInfo[$categoryParams['ENTITY']]['CATEGORY'] = $categoryCode;
                $queryElementsInfo[$categoryParams['ENTITY']]['ELEMENTS'][] = $searchResult['PARAM2'];
            }

            unset($category);
        }

        return $queryElementsInfo;
    }

    /**
     * Запрашивает и сохраняет в arResult дополнительную информацию об элементах в результатах поиска
     * @param array $queryElementsInfo - массив в формате <класс ORM сущности> => ['CATEGORY' => <код категории>, 'ELEMENTS' => <массив id элементов>]
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function queryAdditionalInfo(array $queryElementsInfo)
    {
        if(!$queryElementsInfo) return;

        foreach ($queryElementsInfo as $entityClass => $queryInfo)
        {
            if(!$queryInfo['ELEMENTS']) continue;

            $categoryCode = $queryInfo['CATEGORY'];
            $categoryParams = $this->arParams['CATEGORIES'][$categoryCode];
            $category =& $this->arResult['CATEGORIES'][$categoryCode];

            $dataManager = $categoryParams['ENTITY'] . 'Table';

            if(!NewSmile\Orm\Helper::isDataManagerClass($dataManager))
            {
                throw new Exception('Entity class for category ' . $queryInfo['CATEGORY'] . ' is not correct');
                continue;
            }

            $select = $categoryParams['FIELDS'];
            $select[] = 'ID';

            /**
             * @var \Bitrix\Main\Entity\DataManager $dataManager
             */
            $dbElements = $dataManager::getList(array(
                'select' => $select,
                'filter' => array(
                    'ID' => $queryInfo['ELEMENTS']
                )
            ));

            while($element = $dbElements->fetch())
            {
                foreach ($category['SUBCATEGORIES'] as &$items)
                {
                    if($items[$element['ID']])
                    {
                        $items[$element['ID']] = array_merge($items[$element['ID']], $element);
                    }
                }

                unset($items);
            }

            unset($category);
        }
    }

    protected function sortCategories()
    {
        $categoriesSort = array_flip(array_keys($this->arParams['CATEGORIES']));

        uasort($this->arResult['CATEGORIES'], function($categoryA, $categoryB) use ($categoriesSort)
        {
            if($categoriesSort[$categoryA['CODE']] > $categoriesSort[$categoryB['CODE']])
            {
                return 1;
            }
            elseif($categoriesSort[$categoryA['CODE']] < $categoriesSort[$categoryB['CODE']])
            {
                return -1;
            }
            else
            {
                return 0;
            }
        });
    }

    public function executeComponent()
    {
        if(!$this->arParams['CATEGORIES'])
        {
            ShowError('Not defined categories for search');
            return;
        }

        try
        {
            Loader::includeModule('mmit.newsmile');

            if(NewSmile\Ajax::isAjaxQuery())
            {
                $this->processRequest($this->request);
            }

            $this->includeComponentTemplate();
        }
        catch (Exception $e)
        {
            ShowError($e->getMessage());
        }
    }
}