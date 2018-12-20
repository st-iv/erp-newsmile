<?


namespace Mmit\NewSmile\CommandParam;

class Integer extends Base
{
    protected function formatValue($value)
    {
        return (int)$value;
    }
}