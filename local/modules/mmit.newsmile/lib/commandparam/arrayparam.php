<?


namespace Mmit\NewSmile\CommandParam;

class ArrayParam extends Base
{
    protected function formatValue($value)
    {
        if(!is_array($value))
        {
            $this->sayBadValueType(['array']);
        }

        return $value;
    }

}