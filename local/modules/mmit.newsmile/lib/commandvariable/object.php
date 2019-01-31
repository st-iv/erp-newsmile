<?

namespace Mmit\NewSmile\CommandVariable;

class Object extends Base
{
    /**
     * @var Base[]
     */
    protected $shape;

    public function getTypeName()
    {
        return 'объект';
    }

    public function formatValue($value)
    {
        if(!is_array($value))
        {
            $this->sayBadValueType(['array']);
        }

        return $value;
    }

    /**
     * Устанавливает формат объекта
     * @param Base[] $shape
     * @return Object
     */
    public function setShape(array $shape)
    {
        $this->shape = $shape;
        return $this;
    }

    /**
     * @return Base[]
     */
    public function getShape()
    {
        return $this->shape;
    }
}