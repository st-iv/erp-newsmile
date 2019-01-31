<?


namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Base;

class String extends Base
{
    public function formatValue($value)
    {
        return (string)$value;
    }

    public function getTypeName()
    {
        return 'строка';
    }
}