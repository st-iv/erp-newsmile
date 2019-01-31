<?

namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Base;
use Mmit\NewSmile\Helpers;

class Phone extends Base
{
    public function formatValue($value)
    {
        return Helpers::preparePhone($value);
    }

    public function getTypeName()
    {
        return 'телефон';
    }
}