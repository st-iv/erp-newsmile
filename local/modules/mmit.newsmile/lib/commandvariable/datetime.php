<?


namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Regexp;
use Mmit\NewSmile\CommandVariable\Time;
use Mmit\NewSmile\CommandVariable\Date;

class DateTime extends Regexp
{
    protected function init()
    {
        $this->setRegexp('/^' . Date::getRegexpBody() . ' ' . Time::getRegexpBody() . '$/');
    }

    public function formatValue($value)
    {
        if(($value instanceof \DateTime) || ($value instanceof \Bitrix\Main\Type\Date))
        {
            $value = $value->format('Y-m-d H:i:s');
        }
        else
        {
            $value = parent::formatValue($value);
        }

        return $value;
    }

    public function getTypeName()
    {
        return 'Дата с временем';
    }
}