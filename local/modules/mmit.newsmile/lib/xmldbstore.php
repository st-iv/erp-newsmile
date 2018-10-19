<?
namespace Mmit\NewSmile;


use Bitrix\Main\Application;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Field;
use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Orm\Helper;

class XmlDbStore
{
    protected $baseDir;
    protected $fieldsTypeCache = array();

    /**
     * XmlDbStore constructor.
     *
     * @param string $xmlDir - путь к директории с xml файлами, с которыми будет работать объект. По умолчанию - корневая
     * директория сервера
     *
     * @throws FileNotFoundException
     * @throws \Bitrix\Main\LoaderException
     */
    public function __construct($xmlDir = null)
    {
        if($xmlDir === null)
        {
            $xmlDir = $_SERVER['DOCUMENT_ROOT'];
        }

        if(file_exists($xmlDir))
        {
            $this->baseDir = $xmlDir;
            Loader::includeModule('mmit.newsmile');
        }
        else
        {
            throw new FileNotFoundException($xmlDir);
        }
    }

    /**
     * Сохраняет информацию из бд в xml файл
     * @param Entity $entity - ORM сущность, таблицу которой нужно сохранить
     * @param array $params - массив параметров для getList, который будет использоваться для выборки сохраняемых
     * данных
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function save(Entity $entity, array $params = array())
    {
        /**
         * @var DataManager
         */
        $dataClass = $entity->getDataClass();

        /* получаем названия полей, которые не нужно сохранять в xml (поля с ! перед названием поля в массиве select) */
        $exceptFields = array();

        foreach ($params['select'] as $index => $fieldName)
        {
            if($fieldName[0] == '!')
            {
                $exceptFields[substr($fieldName, 1)] = 1;
                unset($params['select'][$index]);
            }
        }

        if(isset($params['select']) && !$params['select'])
        {
            unset($params['select']);
        }

        /* запрос элементов и запись полей в файл */
        $dbItems = $dataClass::getList($params);

        if(!$dbItems->getSelectedRowsCount()) return;

        $fields = array();

        foreach ($entity->getFields() as $field)
        {
            $fields[$field->getName()] = $field;
        }


        $xmlData = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items />');

        while($item = $dbItems->fetch())
        {
            $itemNode = $xmlData->addChild('item');

            foreach ($item as $fieldName => $fieldValue)
            {
                if(!$exceptFields[$fieldName])
                {
                    $itemNode->addChild($fieldName, $this->convertValueBeforeSave($fieldValue, $fields[$fieldName]));
                }
            }
        }

        $xmlData->saveXML($this->getFileName($entity));
    }

    /**
     * Загрузка информации из xml в бд
     * @param Entity $entity - сущность ORM, которую нужно загрузить
     * @param bool $bTableReload - если true, то таблица будет пересоздана
     * @param bool $bAppend - если true, данные из xml будут добавлены в конец таблицы
     *
     * @throws FileNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function load(Entity $entity, $bTableReload = false, $bAppend = false)
    {
        $dataClass = $entity->getDataClass();
        $fileName = $this->getFileName($entity);

        if(!file_exists($fileName))
        {
            throw new FileNotFoundException($fileName);
        }

        $xmlData = simplexml_load_file($fileName);

        if($bTableReload)
        {
            $connection = Application::getInstance()->getConnection();
            $connection->dropTable($entity->getDBTableName());
            $entity->createDbTable();
        }
        elseif(!$bAppend)
        {
            $dbAllItems = $dataClass::getList(array(
                'select' => $entity->getPrimaryArray()
            ));

            while($item = $dbAllItems->fetch())
            {
                $dataClass::delete($item);
            }
        }

        $fields = array();

        foreach ($entity->getFields() as $field)
        {
            $fields[$field->getName()] = $field;
        }

        foreach ($xmlData->item as $item)
        {
            $addFields = array();
            foreach ($item as $fieldName => $fieldValue)
            {
                if(!$fields[$fieldName] || ($bAppend && $this->isAutocomplete($fields[$fieldName])))
                {
                    continue;
                }

                $addFields[$fieldName] = $this->convertValueBeforeLoad($fieldValue, $fields[$fieldName]);
            }

            if($addFields)
            {
                $dataClass::add($addFields);
            }
        }
    }

    /**
     * Проверяет, является ли поле автозаполняемым
     * @param Field $field
     *
     * @return bool
     */
    protected function isAutocomplete(Field $field)
    {
        return (($field instanceof ScalarField) && $field->isAutocomplete());
    }

    /**
     * Получает имя файла xml для указанной ORM сущности
     * @param Entity $entity
     *
     * @return string
     */
    protected function getFileName(Entity $entity)
    {
        return $this->baseDir . DIRECTORY_SEPARATOR . preg_replace(
            array('/^\\\\mmit\\\\newsmile\\\\/', '/table$/', '/\\\\/'),
            array('', '', '_'),
            strtolower($entity->getDataClass())
        ) . '.xml';
    }

    /**
     * Конвертирует значение поля из xml файла перед загрузкой в БД
     * @param string $value - значение поля
     * @param Field $field - объект описания поля
     *
     * @return Date|DateTime|bool|int|mixed|string
     * @throws \Bitrix\Main\ObjectException
     */
    protected function convertValueBeforeLoad($value, Field $field)
    {
        switch($this->getFieldType($field))
        {
            case 'integer':
                $value = (int)$value;
                break;

            case 'date':
                $value = new Date($value);
                break;

            case 'datetime':
                $value = new DateTime($value);
                break;

            case 'boolean':
                $value = ($value == true);
                break;

            default:
                $value = (string)$value;
        }

        if($field->isSerialized())
        {
            $value = unserialize($value);
        }

        return $value;
    }

    /**
     * Конвертирует значение поля из БД перед записью в xml
     * @param string $value - значение поля
     * @param Field $field - объект описания поля
     *
     * @return string
     */
    protected function convertValueBeforeSave($value, Field $field)
    {
        if($field->isSerialized())
        {
            $value = serialize($value);
        }

        return $value;
    }

    /**
     * Получает и кеширует тип поля в fieldsTypeCache
     * @param Field $field - объект описания поля
     *
     * @return mixed
     */
    protected function getFieldType(Field $field)
    {
        $fieldName = $field->getName();

        $dataManager = $field->getEntity()->getDataClass();

        if(!$this->fieldsTypeCache[$dataManager][$fieldName])
        {
            $this->fieldsTypeCache[$dataManager][$fieldName] = Helper::getFieldType($field);
        }

        return $this->fieldsTypeCache[$dataManager][$fieldName];
    }

}