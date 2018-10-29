<?
namespace Mmit\NewSmile\Notice;

use Bitrix\Main\Entity;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;

class TypeTable extends Entity\DataManager implements ExtendedFieldsDescriptor
{
    protected static $typesInfo = array();
    protected static $codeIdMap = array();

    protected static $enumVariantsTitles = array(
        'GROUP' => array(
            'VISIT' => 'Приёмы',
            'SYSTEM' => 'Системные',
            'BOSSES' => 'Начальство',
            'CALLS' => 'Обзвон'
        )
    );

    public static function getTableName()
    {
        return 'm_newsmile_notice_type';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\StringField('CODE', array(
                'required' => true,
                'title' => 'Код',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 100),
                    );
                },
            )),
            new Entity\StringField('TITLE', array(
                'required' => true,
                'title' => 'Заголовок',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\TextField('TEXT', array(
                'title' => 'Текст',
            )),
            new Entity\EnumField('GROUP', array(
                'title' => 'Группа',
                'values' => array_keys(static::getEnumVariants('GROUP'))
            )),
        );
    }

    /**
     * Получает все типы уведомлений в кешируемом запросе
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected static function initTypesInfo()
    {
        if(!static::$typesInfo)
        {
            $dbAllTypes = static::getList(array(
                'cache' => array(
                    'ttl' => 360000000
                )
            ));

            while ($typeInfo = $dbAllTypes->fetch())
            {
                static::$codeIdMap[$typeInfo['CODE']] = $typeInfo['ID'];
                static::$typesInfo[$typeInfo['ID']] = $typeInfo;
                unset(static::$typesInfo[$typeInfo['ID']]['ID']);
            }
        }
    }

    /**
     * Возвращает все поля по определенному типу уведомлений. Использует встроенное кеширование ORM.
     * @param int|string $type - id или символьный код типа
     *
     * @return array|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getTypeInfo($type)
    {
        static::initTypesInfo();
        return static::$typesInfo[$type] ?: static::$typesInfo[static::$codeIdMap[$type]];
    }

    public static function getCodeById($id)
    {
        static::initTypesInfo();
        return static::$typesInfo[$id]['CODE'];
    }

    public static function getIdByCode($code)
    {
        static::initTypesInfo();
        return static::$codeIdMap[$code];
    }

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumVariantsTitles[$enumFieldName];
    }
}