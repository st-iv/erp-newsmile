<?


namespace Mmit\NewSmile\CommandVariable;

use Bitrix\Main\Diag\Debug;
use Mmit\NewSmile\CommandVariable\Base;

class ArrayParam extends Base
{
    /**
     * @var Base
     */
    protected $contentType;

    public function formatValue($value)
    {
        if(!is_array($value))
        {
            $this->sayBadValueType(['array']);
        }

        return $value;
    }

    public function setContentType(Base $contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getTypeName()
    {
        $result = 'массив';

        if(isset($this->contentType))
        {
            $result .= ' ' . $this->contentType->getTypeNameGenitive();
        }

        return $result;
    }

    public function getTypeNameGenitive()
    {
        return 'массивов';
    }

    public function getPrintValue($value)
    {
        if($this->contentType)
        {
            array_walk($value, function(&$item, $key)
            {
                $item = $this->contentType->getPrintValue($item);
            });
        }

        return '[' . implode(', ', $value) . ']';
    }
}