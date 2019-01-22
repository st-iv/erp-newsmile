<?

namespace Mmit\NewSmile\Search;

use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ORM\Entity;
use Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile;

/**
 * Управляет индексацией таблиц модуля внутренним поиском битрикса
 * Class Manager
 * @package Mmit\NewSmile\Search
 */
class Manager
{
    /**
     * Возвращает GetList команды проиндексированных ORM сущностей системы
     *
     * @return Entity[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getSearchableEntities()
    {
        return [PatientCardTable::getEntity(), DoctorTable::getEntity()];
    }


    public static function handleEntityAdd(Event $event)
    {
        $entity = static::getEntityByEventType($event->getEventType());
        $primary = $event->getParameter('primary');

        static::indexSearch($primary['ID'], $entity,  $event->getParameter('fields'));
    }

    public static function handleEntityUpdate(Event $event)
    {
        $entity = static::getEntityByEventType($event->getEventType());
        $primary = $event->getParameter('primary');

        $dataManager = $entity->getDataClass();
        $fields = $dataManager::getById($primary['ID'])->fetch();

        static::indexSearch($primary['ID'], $entity,  $fields);
    }

    public static function handleEntityDelete(Event $event)
    {
        $entity = static::getEntityByEventType($event->getEventType());
        $primary = $event->getParameter('primary');

        static::deleteSearchIndex($primary['ID'], $entity);
    }


    /**
     * Добавляет запись в поисковый индекс в формате, необходимом для возможности раздельного поиска по определённым полям
     *
     * @param int $id - id записи
     * @param Entity $entity - объект сущности
     * @param array $fields - основной набор полей, для которых будет создана общая запись в поисковом индексе
     * @param callable $callback - функция, которой будут переданы данные для индексации. Набор параметров смотри в документации метода \CSearch::Index
     *
     * @throws LoaderException
     */
    protected static function indexSearch($id, $entity, $fields, $callback = null)
    {
        $id = (string)$id;

        $callback = $callback ?: ['\\CSearch', 'Index'];

        Loader::includeModule('search');

        $category = static::getSearchCategory($entity);

        /**
         * @var Searchable $dataManager
         */
        $dataManager = $entity->getDataClass();

        call_user_func(
            $callback,
            "mmit.newsmile",
            $category . '_' . $id,
            [
                "DATE_CHANGE" => date('d.m.Y'),
                "TITLE" => $dataManager::getMainIndex($fields),
                "SITE_ID" => NewSmile\Config::getSiteId(),
                "PARAM1" => $category,
                "PARAM2" => $id,
                "URL" => '',
                "BODY" => '',
            ],
            true
        );

        foreach ($dataManager::getSearchableFields() as $fieldName)
        {
            $fieldValue = $fields[$fieldName];
            $fieldName = strtolower($fieldName);

            call_user_func(
                $callback,
                "mmit.newsmile",
                $category . '_' . $fieldName . '_' . $id,
                [
                    "DATE_CHANGE" => date('d.m.Y'),
                    "TITLE" => $fieldValue,
                    "SITE_ID" => NewSmile\Config::getSiteId(),
                    "PARAM1" => $category,
                    "PARAM2" => $id,
                    "URL" => '',
                    "BODY" => '',
                ],
                true
            );
        }
    }

    /**
     * Запускает индексацию всех элементов системы
     * @param array $categories - категории поиска, которые нужно проиндексировать. По умолчанию - все категории
     * @param array $filters - массив фильтров в формате категория поиска =>
     * @param callable $callback - обработчик, которому будут переданы параметры записи индекса. По умолчанию \CSearch::Index
     *
     * @throws LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function indexAll(array $categories = [], array $filters = [], $callback = null)
    {
        foreach (static::getSearchableEntities() as $entity)
        {
            $category = static::getSearchCategory($entity);

            if($categories && !in_array($category, $categories)) continue;

            $dataManager = $entity->getDataClass();
            $queryParams = [];

            if($filters[$category])
            {
                $queryParams['filter'] = $filters[$category];
            }

            $dbItems = $dataManager::getList();

            while($item = $dbItems->fetch())
            {
                static::indexSearch($item['ID'], $entity, $item, $callback);
            }
        }
    }



    /**
     * Удаляет поисковый индекс элемента с указанным id в указанной категории
     * @param string $id
     * @param Entity $entity
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function deleteSearchIndex($id, $entity)
    {
        Loader::includeModule('search');
        $category = static::getSearchCategory($entity);

        /* прямой запрос к бд, тк удаление индекса по PARAM1 и PARAM2 не работает, если заполнен ITEM_ID */

        $indexItems = Application::getConnection()->query('
            SELECT sc.ITEM_ID
			FROM b_search_content sc
			WHERE sc.MODULE_ID = \'mmit.newsmile\'
            AND (
                sc.PARAM1 = \'' . $category . '\'
                AND sc.PARAM2 = \'' . $id . '\'
            )'
        );

        while($indexItem = $indexItems->fetch())
        {
            Debug::writeToFile($indexItem, '$indexItem');
            \CSearch::DeleteIndex(
                'mmit.newsmile',
                $indexItem['ITEM_ID']
            );
        }
    }

    /**
     * Получает категорию поиска указанной ORM сущности
     * @param Entity $entity
     *
     * @return string
     */
    public static function getSearchCategory(Entity $entity)
    {
        $shortClassName = NewSmile\Helpers::getShortClassName($entity->getDataClass());
        return strtolower(preg_replace('/Table$/', '', $shortClassName));
    }

    /**
     * Регистрирует / удаляет подписку на события
     * @param bool $bRegister - если true - регистрирует обработчики, необходимые для работы поиска, иначе отменяет подписку
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected static function toggleDependences($bRegister = true)
    {
        $method = ($bRegister ? 'registerEventHandler' : 'unRegisterEventHandler');

        $eventManager = EventManager::getInstance();
        $eventManager->$method('search', 'OnReIndex', 'mmit.newsmile', self::class, 'reIndex');

        foreach (static::getSearchableEntities() as $entity)
        {
            $eventPrefix = preg_replace('/Table$/', '', $entity->getDataClass()) . '::';
            $eventManager->$method('mmit.newsmile', $eventPrefix . 'OnAfterAdd', 'mmit.newsmile', self::class, 'handleEntityAdd');
            $eventManager->$method('mmit.newsmile', $eventPrefix . 'OnAfterUpdate', 'mmit.newsmile', self::class, 'handleEntityUpdate');
            $eventManager->$method('mmit.newsmile', $eventPrefix . 'OnAfterDelete', 'mmit.newsmile', self::class, 'handleEntityDelete');
        }
    }

    /**
     * Регистрирует обработчики, необходимые для работы поиска
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function registerDependences()
    {
        static::toggleDependences();
    }

    /**
     * Отменяет подписку на события, необходимые для работы поиска
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function unRegisterDependences()
    {
        static::toggleDependences(false);
    }

    /**
     * Обработчик события переиндексации поиска
     *
     * @param $stepInfo
     * @param $callbackObject
     * @param $callbackMethod
     *
     * @throws LoaderException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function reIndex($stepInfo, $callbackObject, $callbackMethod)
    {
        $categories = [];
        $filters = [];

        if(($stepInfo['MODULE'] == 'mmit.newsmile') && ($stepInfo['ID']))
        {
            if(preg_match('/([A-Za-z0-9_]+)_([0-9]+)$/', $stepInfo['ID'], $matches))
            {
                $category = $matches[1];
                $categories = [$category];
                $filters = [
                    $category => [
                        'ID' => (int)$matches[2]
                    ]
                ];
            }
            else
            {
                return;
            }
        }

        static::indexAll($categories, $filters, function($moduleId, $itemId, $fields, $bOverwrite) use($callbackObject, $callbackMethod)
        {
            $fields['ID'] = $itemId;
            call_user_func([$callbackObject, $callbackMethod], $fields);
        });
    }

    /**
     * Возвращает ORM сущность по типу события
     * @param string $eventType
     *
     * @return Entity
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected static function getEntityByEventType($eventType)
    {
        /**
         * @var DataManager $dataManager
         */
        $dataManager = substr($eventType, 0, strpos($eventType, '::')) . 'Table';
        return $dataManager::getEntity();
    }


}