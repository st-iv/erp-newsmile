<?


namespace Mmit\NewSmile\CommandVariable;

use Bitrix\Main\Diag\Debug;
use Mmit\NewSmile\CommandVariable\Base;

class ArrayParam extends Base
{
    /**
     * @var Base
     */
    protected $contentVariable;

    public function formatValue($value)
    {
        if(!is_array($value))
        {
            $this->sayBadValueType(['array']);
        }

        return $value;
    }

    public function setContentVariable(Base $contentVariable)
    {
        $this->contentVariable = $contentVariable;
        return $this;
    }

    public function getContentVariable()
    {
        return $this->contentVariable;
    }

    public function getTypeName()
    {
        return 'массив';
    }

    protected function getPrintValue($value)
    {
        return '[' . implode(', ', $value) . ']';
    }
}