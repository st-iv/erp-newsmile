<?


namespace Mmit\NewSmile\Orm\Fields;

use Bitrix\Main\Entity\StringField;
use Mmit\NewSmile\Helpers;

class PhoneField extends StringField
{
    public function __construct($name, array $parameters = array())
    {
        parent::__construct($name, $parameters);
        $this->addSaveDataModifier(function($value)
        {
            return Helpers::preparePhone($value);
        });
    }
}