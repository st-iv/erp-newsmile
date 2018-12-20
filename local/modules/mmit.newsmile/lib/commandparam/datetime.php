<?


namespace Mmit\NewSmile\CommandParam;

class DateTime extends Regexp
{
    protected function init()
    {
        $this->setRegexp('/^' . Date::getRegexpBody() . ' ' . Time::getRegexpBody() . '$/');
    }

    protected function formatValue($value)
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
}