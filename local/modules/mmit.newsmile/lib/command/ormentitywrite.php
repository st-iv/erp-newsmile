<?

namespace Mmit\NewSmile\Command;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\Field;
use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\ORM\Entity;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\CommandParam;
use Mmit\NewSmile\Orm\Helper;

abstract class OrmEntityWrite extends Base
{
    protected function getFieldsValues()
    {
        $result = [];

        $paramsMap = $this->getParamsMapAssoc();

        foreach ($this->params as $paramKey => $paramValue)
        {
            /**
             * @var \Mmit\NewSmile\CommandParam\Base $param
             */
            $param = $paramsMap[$paramKey];

            if($param instanceof CommandParam\Date)
            {
                $paramValue = new \Bitrix\Main\Type\Date($paramValue, 'Y-m-d');
            }
            elseif($param instanceof CommandParam\DateTime)
            {
                $paramValue = new \Bitrix\Main\Type\DateTime($paramValue, 'Y-m-d H:i:s');
            }


            $result[Helpers::getSnakeCase($paramKey)] = $paramValue;
        }

        return $result;
    }

    public function getParamsMap()
    {
        $entity = $this->getOrmEntity();
        if(!$entity) return [];

        $result = [];
        $fields = array_filter($entity->getFields(), function(Field $field)
        {
            return ($field instanceof ScalarField) && $this->filterField($field);
        });

        foreach ($fields as $field)
        {
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

        if($field->isSerialized())
        {
            $class = CommandParam\ArrayParam::class;
        }
        else
        {
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

                case 'multipleenum':
                    $class = CommandParam\ArrayParam::class;
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

                case 'phone':
                    $class = CommandParam\Phone::class;
                    break;

                default:
                    throw new Error('Неподдерживаемый тип поля orm сущности: ' . $fieldType, 'NOT_SUPPORTED_FIELD_TYPE');
            }
        }


        return new $class(
            $this->getParamNameByField($field),
            $field->getTitle(),
            '',
            $field->isRequired()/*,
            $field->getDefaultValue()*/
        );
    }

    /**
     * Получает имя параметра команды, соответствующего указанному полю
     * @param Field $field
     *
     * @return string
     */
    protected function getParamNameByField(Field $field)
    {
        return Helpers::getCamelCase($field->getName(), false);
    }

    /**
     * Возвращает Entity, с которой будет работать команда
     * @return Entity
     */
    abstract protected function getOrmEntity();

    /**
     * Возвращает true для полей, к которым команда должна предоставить доступ
     * @param Field $field
     *
     * @return bool
     */
    abstract protected function filterField(Field $field);
}