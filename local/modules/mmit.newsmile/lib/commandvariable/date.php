<?

namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Regexp;

class Date extends Regexp
{
    protected static $dateRegexpBody = '[0-9]{4}-[0-9]{2}-[0-9]{2}';

    protected function init()
    {
        $this->setRegexp('/^' . static::$dateRegexpBody . '$/');
    }

    public function formatValue($value)
    {
        if(($value instanceof \DateTime) || ($value instanceof \Bitrix\Main\Type\Date))
        {
            $value = $value->format('Y-m-d');
        }
        else
        {
            $value = parent::formatValue($value);

            $dateParts = explode('-', $value);

            if(!checkdate($dateParts[1], $dateParts[2], $dateParts[0]))
            {
                $this->sayBadValueFormat('корректная дата в формате YYYY-MM-DD');
            }
        }

        return $value;
    }

    public static function getRegexpBody()
    {
        return static::$dateRegexpBody;
    }

    public function getTypeName()
    {
        return 'дата';
    }
}