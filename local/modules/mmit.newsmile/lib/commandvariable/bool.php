<?

namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Base;

class Bool extends Base
{
    public function formatValue($value)
    {
        if(is_string($value))
        {
            $value = ($value == 'true');
        }
        else
        {
            $value = ($value == true);
        }

        return $value;
    }

    protected function getPrintValue($value)
    {
        return ($value ? 'true' : 'false');
    }

    public function getTypeName()
    {
        return 'булево';
    }
}