<?


namespace Mmit\NewSmile\Orm\Fields;

use Bitrix\Main\Entity\TextField;
use Bitrix\Main\SystemException;
use Bitrix\Main\ORM\Fields\Validators;
use Mmit\NewSmile\Orm\Fields\Validators\MultipleEnumValidator;
use Bitrix\Main\ORM\Fields\Validators\EnumValidator;


class MultipleEnumField extends TextField
{
    protected $values;

    /**
     * EnumField constructor.
     *
     * @param       $name
     * @param array $parameters deprecated, use configure* and add* methods instead
     *
     * @throws SystemException
     */
    function __construct($name, $parameters = array())
    {
        $parameters['serialized'] = true;

        parent::__construct($name, $parameters);

        if (isset($parameters['values']))
        {
            $this->values = $parameters['values'];
        }
    }

    public function postInitialize()
    {
        if (!is_array($this->values))
        {
            throw new SystemException(sprintf(
                'Parameter "values" for %s field in `%s` entity should be an array',
                $this->name, $this->entity->getDataClass()
            ));
        }

        if (empty($this->values))
        {
            throw new SystemException(sprintf(
                'Required parameter "values" for %s field in `%s` entity is not found',
                $this->name, $this->entity->getDataClass()
            ));
        }
    }

    /**
     * @param $values
     *
     * @return $this
     */
    public function configureValues($values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @return array|Validators\Validator[]|callback[]
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function getValidators()
    {
        $validators = parent::getValidators();

        if ($this->validation === null)
        {
            $validators[] = new MultipleEnumValidator();
        }

        return $validators;
    }

    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function cast($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertValueFromDb($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     * @throws SystemException
     */
    public function convertValueToDb($value)
    {
        return $this->getConnection()->getSqlHelper()->convertToDbString($value);
    }
}