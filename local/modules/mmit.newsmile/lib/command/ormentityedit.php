<?

namespace Mmit\NewSmile\Command;

use Bitrix\Main\Entity\Field;
use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Orm\Helper;
use Mmit\NewSmile\CommandParam;

/**
 * Составляет карту параметров по полям Entity, возвращаемой методом getOrmEntity
 *
 * Class OrmEntityAdd
 * @package Mmit\NewSmile\Command
 */
abstract class OrmEntityEdit extends Base
{
    protected function doExecute()
    {
        // TODO: Implement doExecute() method.
    }

    public function getParamsMap()
    {
        $entity = $this->getOrmEntity();
        if(!$entity) return [];

        $result = [];

        foreach ($entity->getFields() as $field)
        {
            if(!($field instanceof ScalarField)) continue;
            $result[] = $this->getParamByField($field);
        }

        return $result;
    }

    /**
     * @param ScalarField $field
     *
     * @return CommandParam\Base
     * @throws Error
     */
    protected function getParamByField(ScalarField $field)
    {
        $class = null;
        $fieldType = Helper::getFieldType($field);

        switch($fieldType)
        {
            case 'integer':
                $class = CommandParam\Integer::class;
                break;

            case 'float':
                $class = CommandParam\Float::class;
                break;

            case 'string':
            case 'text':
            case 'enum':
                $class = CommandParam\String::class;
                break;

            case 'date':
                $class = CommandParam\Date::class;
                break;

            case 'datetime':
                $class = CommandParam\DateTime::class;
                break;

            case 'boolean':
                $class = CommandParam\Bool::class;
                break;

            default:
                throw new Error('Неподдерживаемый тип поля orm сущности: ' . $fieldType, 'NOT_SUPPORTED_FIELD_TYPE');
        }

        return new $class(
            Helpers::getCamelCase($field->getName(), false),
            $field->getTitle(),
            '',
            $field->isRequired()/*,
            $field->getDefaultValue()*/
        );
    }

    /**
     * Возвращает Entity, по полям которой будет строиться карта параметров
     * @return Entity
     */
    abstract protected function getOrmEntity();
}