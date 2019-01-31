<?


namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Base;

class Integer extends Base
{
    public function formatValue($value)
    {
        return (int)$value;
    }

    public function getTypeName()
    {
        return 'целое число';
    }
}