<?

namespace Mmit\NewSmile\CommandParam;


class Any extends Base
{
    protected function formatValue($value)
    {
        return $value;
    }
}