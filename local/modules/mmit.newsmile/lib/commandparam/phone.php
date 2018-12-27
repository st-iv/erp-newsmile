<?

namespace Mmit\NewSmile\CommandParam;

use Mmit\NewSmile\Helpers;

class Phone extends Base
{
    protected function formatValue($value)
    {
        return Helpers::preparePhone($value);
    }
}