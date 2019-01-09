<?

namespace Mmit\NewSmile\Orm\Fields\Validators;

use Bitrix\Main\ORM;

class MultipleEnumValidator extends ORM\Fields\Validators\Validator
{
    /**
     * @param $value
     * @param $primary
     * @param array $row
     * @param \Bitrix\Main\ORM\Fields\Field | \Bitrix\Main\ORM\Fields\EnumField | \Bitrix\Main\ORM\Fields\BooleanField $field
     *
     * @return bool|string
     */
    public function validate($value, $primary, array $row, ORM\Fields\Field $field)
    {
        if (!count(array_diff($value, $field->getValues())) || $value == '')
        {
            return true;
        }

        return $this->getErrorMessage($value, $field);
    }
}