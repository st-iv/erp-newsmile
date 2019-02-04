<?

namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Base;
use Mmit\NewSmile\Helpers;

class Phone extends Base
{
    public function formatValue($value)
    {
        $value = Helpers::preparePhone($value);
        if(strlen($value) !== 11)
        {
            $this->sayBadValueFormat('11 цифр');
        }
    }

    public function getTypeName()
    {
        return 'телефон';
    }

    public function getTypeNameGenitive()
    {
        return 'телефонов';
    }

    public function getTypeDescription()
    {
        return 'обязательно должен содержать ровно 11 цифр';
    }
}