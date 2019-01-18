<?

namespace Mmit\NewSmile\Command\Search;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Entity;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command;
use Mmit\NewSmile\CommandParam;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Orm\Helper;
use Mmit\NewSmile\PatientCardTable;

class Search extends Base
{
    protected static $name = 'Поиск';
    protected static $description = 'Осуществляет поиск по всем проиндексированным сущностям системы.';

    protected $categoriesEntitiesMap = null;

    protected function doExecute()
    {
        if(!Loader::includeModule('search'))
        {
            throw new Error('Не установлен модуль поиска', 'SEARCH_MODULE_NOT_INSTALLED');
        }

        /* подготовка поисковой строки */

        $query = ltrim($this->params['query']);
        if(empty($query)) return;

        \CUtil::decodeURIComponent($query);
        $query = urldecode($query);

        if($this->params['useLanguageGuess'])
        {
            $arLang = \CSearchLanguage::GuessLanguage($query);
            if(is_array($arLang) && $arLang["from"] != $arLang["to"])
            {
                $query = \CSearchLanguage::ConvertKeyboardLayout($query, $arLang["from"], $arLang["to"]);
            }
        }

        // оборачиваем всю поисковую строку в двойные кавычки, чтобы работал поиск по части слова
        $query = preg_replace('/(\S+)/', '"\\1"', $query);

        /* поиск */

        $searchExFilter = [
            'PARAM1' => $this->params['categories']
        ];

        $search = new \CSearch();
        $search->Search(
            array(
                'QUERY' => $query,
                'MODULE_ID' => 'mmit.newsmile',
            ),
            array(),
            $searchExFilter
        );

        /* запрос дополнительной информации и формирование результата */

        if($search->error)
        {
            throw new Error('При проведении поиска возникла ошибка: ' . $search->error, 'SEARCH_ERROR');
        }
        else
        {
            $this->writeSearchResults($search);
            $this->queryAdditionalInfo();
            $this->sortCategories();
        }
    }

    /**
     * Пишет результаты поиска в arResult, подготавливает данные для запроса дополнительной информации
     *
     * @param \CSearch $search
     *
     * @return array данные для запроса дополнительной информации
     */
    protected function writeSearchResults(\CSearch $search)
    {
        while($searchResult = $search->Fetch())
        {
            $categoryCode = $searchResult['PARAM1'];
            $category =& $this->result[$categoryCode];

            if(preg_match('/^' . $categoryCode . '_([a-z_]+)_' . $searchResult['PARAM2'] .'$/', $searchResult['ITEM_ID'], $matches))
            {
                $subCategoryName = $matches[1];
            }
            else
            {
                $subCategoryName = 'main';
            }
            $category[$subCategoryName][$searchResult['PARAM2']] = array(
                'id' => $searchResult['PARAM2'],
                'searchEntry' => $searchResult['TITLE_FORMATED']
            );

            unset($category);
        }
    }

    /**
     * Запрашивает и сохраняет в result дополнительную информацию об элементах в результатах поиска
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function queryAdditionalInfo()
    {
        if(!$this->params['select']) return;

        $queryMap = [];

        foreach ($this->result as $categoryCode => $categoryResults)
        {
            if($this->params['select'][$categoryCode])
            {
                foreach ($categoryResults as $subcategoryItems)
                {
                    if(!isset($queryMap[$categoryCode]))
                    {
                        $queryMap[$categoryCode] = [];
                    }

                    $queryMap[$categoryCode] = array_merge($queryMap[$categoryCode], array_keys($subcategoryItems));
                }
            }
        }

        foreach ($queryMap as $categoryCode => $ids)
        {
            if(!$ids) continue;

            $getListCommandClass = $this->getCommandClassByCategory($categoryCode);
            $select = $this->params['select'][$categoryCode];
            $select[] = 'id';

            /**
             * @var Command\OrmGetList $command
             */
            $command = new $getListCommandClass([
                'filter' => [
                    'id' => $ids
                ],
                'select' => $select
            ]);

            $command->execute();
            $commandResult = $command->getResult();

            foreach ($commandResult['list'] as $item)
            {
                foreach ($this->result[$categoryCode] as &$subcategoryItems)
                {
                    if($subcategoryItems[$item['id']])
                    {
                        $subcategoryItems[$item['id']] = array_merge($subcategoryItems[$item['id']], $item);
                    }
                }

                unset($subcategoryItems);
            }
        }
    }

    /**
     * Сортирует категории в том порядке, в котором они были указаны в параметре categories
     */
    protected function sortCategories()
    {
        if(!$this->params['categories']) return;

        $categoriesSort = array_flip($this->params['categories']);

        uasort($this->result, function($categoryA, $categoryB) use ($categoriesSort)
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

    public function getParamsMap()
    {
        return [
            new CommandParam\String('query', 'поисковая строка', '', true),
            new CommandParam\Bool('useLanguageGuess', 'флаг восстановления раскладки', '', false, true),
            new CommandParam\ArrayParam(
                'categories',
                'категории',
                'коды категорий поиска',
                false,
                []
            ),
            new CommandParam\ArrayParam(
                'select',
                'дополнительные поля результатов для выборки',
                'Формат объекта: <код категории> => <массив полей для выборки>'
            )
        ];
    }

    /**
     * Возвращает GetList команды проиндексированных ORM сущностей системы
     *
     * @return Entity[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getSearchableEntities()
    {
        return [PatientCardTable::getEntity(), DoctorTable::getEntity()];
    }

    /**
     * Возвращает класс GetList команды по коду поисковой категории
     * @param string $categoryCode
     *
     * @return null|string
     */
    protected function getCommandClassByCategory($categoryCode)
    {
        if(!isset($this->categoriesEntitiesMap))
        {
            foreach ($this->getSearchableEntities() as $entity)
            {
                $this->categoriesEntitiesMap[Helper::getSearchCategory($entity)] = Helper::getCommandNamespaceByEntity($entity) . '\\GetList';
            }
        }

        return $this->categoriesEntitiesMap[$categoryCode];
    }
}