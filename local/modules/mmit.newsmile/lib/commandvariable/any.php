<?

namespace Mmit\NewSmile\CommandVariable;


use Mmit\NewSmile\CommandVariable\Base;

class Any extends Base
{
    public function formatValue($value)
    {
        return $value;
    }

    public function getTypeName()
    {
        return 'любой';
    }
}