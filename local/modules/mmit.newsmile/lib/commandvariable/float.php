<?

namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Base;

class Float extends Base
{
    public function formatValue($value)
    {
        return (float)$value;
    }

    public function getTypeName()
    {
        return 'число';
    }

    public function getTypeNameGenitive()
    {
        return 'чисел';
    }
}