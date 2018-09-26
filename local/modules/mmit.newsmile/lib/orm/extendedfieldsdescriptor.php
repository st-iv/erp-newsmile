<?

namespace Mmit\NewSmile\Orm;

/**
 * Предоставляет дополнительную информацию о полях ORM сущности
 * Interface ExtendedFieldsDescriptor
 * @package Mmit\NewSmile\Orm
 */
interface ExtendedFieldsDescriptor
{
    /**
     * Возвращает названия пунктов enum поля
     * @param $enumFieldName
     * @return mixed
     */
    public static function getEnumVariantsTitles($enumFieldName);
}