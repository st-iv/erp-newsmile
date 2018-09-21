<?

namespace Mmit\NewSmile\Orm;

abstract class FieldsVisitor
{
    protected $params = array();

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function visit(\Bitrix\Main\ORM\Fields\Field $field)
    {
        $fieldClass = get_class($field);
        $specificMethodName = 'visit' . substr($fieldClass, strrpos($fieldClass, '\\') + 1);

        if($this->visitField($field) && method_exists($this, $specificMethodName))
        {
            $this->$specificMethodName($field);
        }
    }

    abstract protected function visitField(\Bitrix\Main\ORM\Fields\Field $field);
}