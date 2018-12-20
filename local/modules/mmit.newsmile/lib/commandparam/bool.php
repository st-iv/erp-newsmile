<?

namespace Mmit\NewSmile\CommandParam;

class Bool extends Base
{
    protected function formatValue($value)
    {
        if(!is_bool($value))
        {
            if(is_string($value))
            {
                $value = ($value == 'true');
            }
            else
            {
                $this->sayBadValueType(['bool', 'string']);
            }
        }

        return $value;
    }

}