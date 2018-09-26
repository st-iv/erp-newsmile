<?

namespace Mmit\NewSmile\Orm\Fields;


use Bitrix\Main\Entity\Field;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\FieldTypeMask;

/**
 * Фиктивное поле ORM сущности, обозначает собой "привязавшуюся" сущность - сущность, у которой есть привязка к данной сущности.
 * Предназначено для вывода полей привязавшейся сущности среди полей базовой сущности ( масло масляное =/ )
 * Class ReverseReference
 * @package Mmit\NewSmile\Orm\Fields
 */
class ReverseReference extends Field
{
    /**
     * @var Entity
     */
    protected $sourceEntity;

    /**
     * @var Field
     */
    protected $keyField;

    public function __construct($name, $parameters = array())
    {
        parent::__construct($name, $parameters);

        if (isset($parameters['source_entity']) && $parameters['source_entity'] instanceof Entity)
        {
            $this->sourceEntity = $parameters['source_entity'];
        }

        if(isset($parameters['key_field']) && $parameters['key_field'] instanceof Field)
        {
            $this->keyField = $parameters['key_field'];
        }
    }

    public function getSourceEntity()
    {
        return $this->sourceEntity;
    }

    public function getKeyField()
    {
        return $this->keyField;
    }

    public function getTypeMask()
    {
        return FieldTypeMask::REFERENCE;
    }
}