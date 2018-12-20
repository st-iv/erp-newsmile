<?


namespace Mmit\NewSmile\CommandParam;

class String extends Base
{
    protected function formatValue($value)
    {
        return (string)$value;
    }
}