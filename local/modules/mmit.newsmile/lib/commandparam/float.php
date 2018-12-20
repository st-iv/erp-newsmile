<?

namespace Mmit\NewSmile\CommandParam;

class Float extends Base
{
    protected function formatValue($value)
    {
        return (float)$value;
    }

}