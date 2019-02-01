<?

namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\Error;

class Enum extends String
{
    protected $variants;

    public function setVariants(array $variants)
    {
        $this->variants = $variants;
        return $this;
    }

    public function formatValue($value)
    {
        if(!$this->variants)
        {
            $message = 'Для параметра ' . $this->code;

            if($this->command)
            {
                $message .= ' команды ' . $this->command->getCode();
            }

            $message .=  ' не задан список допустимых значений';

            throw new Error($message, 'ENUM_VARIANTS_NOT_DEFINED');
        }

        if(!in_array($value, $this->variants))
        {
            $this->sayBadValueFormat('значение из списка (' . implode(', ', $this->variants) . ')');
        }

        return $value;
    }

    public function getTypeName()
    {
        return 'перечисление ["' . implode('", "', $this->variants) . '"]';
    }

    public function getTypeNameGenitive()
    {
        return 'перечислений';
    }
}