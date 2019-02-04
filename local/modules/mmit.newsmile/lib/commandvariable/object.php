<?

namespace Mmit\NewSmile\CommandVariable;

class Object extends Base
{
    /**
     * @var Base[]
     */
    protected $shape;

    /**
     * @var bool
     */
    protected $isFlexible = false;

    public function getTypeName()
    {
        return 'объект';
    }

    public function getTypeNameGenitive()
    {
        return 'объектов';
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

    public function removeShapeFields(array $codes)
    {
        $codes = array_flip($codes);

        foreach ($this->shape as $index => $field)
        {
            if(isset($codes[$field->getCode()]))
            {
                unset($this->shape[$index]);
            }
        }
    }

    /**
     * @return mixed
     */
    public function isFlexible()
    {
        return $this->isFlexible;
    }

    /**
     * Указывает является ли структура объекта не до конца определённой (набор полей меняется в зависимости от условий)
     *
     * @param bool $isFlexible
     *
     * @return Object
     */
    public function setFlexible($isFlexible)
    {
        $this->isFlexible = $isFlexible;
        return $this;
    }

}