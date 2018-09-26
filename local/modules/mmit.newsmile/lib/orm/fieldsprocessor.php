<?

namespace Mmit\NewSmile\Orm;

use Bitrix\Main\Entity\Field;

abstract class FieldsProcessor
{
    protected $params = array();
    protected $result = array();

    /**
     * @var Field[]
     */
    protected $fields = array();

    public function __construct(array $params)
    {
        $this->setParams($params);
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setParams(array $params)
    {
        foreach ($params as $paramName => $paramValue)
        {
            $this->setParam($paramName, $paramValue);
        }
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;

        if(($name == 'SELECT_FIELDS') || ($name == 'EDITABLE_FIELDS'))
        {
            if($this->params['EDITABLE_FIELDS'])
            {
                if($this->params['SELECT_FIELDS'])
                {
                    $this->params['SELECT_FIELDS'] = array_merge($this->params['SELECT_FIELDS'], $this->params['EDITABLE_FIELDS']);
                }
                else
                {
                    $this->params['SELECT_FIELDS'] = $this->params['EDITABLE_FIELDS'];
                }
            }
        }
    }

    public function process(\Bitrix\Main\ORM\Fields\Field $field)
    {
        $specificMethodName = 'process' . $this->getShortClassName($field);

        $result = $this->processField($field);

        if($result)
        {
            if(method_exists($this, $specificMethodName))
            {
                $specificMethodResult = $this->$specificMethodName($field);

                if(is_array($result) && is_array($specificMethodResult))
                {
                    $result = array_merge($result, $specificMethodResult);
                }
                elseif($specificMethodResult || $specificMethodResult === false)
                {
                    $result = $specificMethodResult;
                }
            }

            if($result)
            {
                $this->result[$field->getName()] = $result;
            }

            $this->fields[$field->getName()] = $field;
        }
    }

    protected function getShortClassName(Field $field)
    {
        $fieldClass = get_class($field);
        return substr($fieldClass, strrpos($fieldClass, '\\') + 1);
    }

    abstract protected function processField(\Bitrix\Main\ORM\Fields\Field $field);
}