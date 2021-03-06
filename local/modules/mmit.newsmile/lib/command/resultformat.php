<?


namespace Mmit\NewSmile\Command;

use Mmit\NewSmile\CommandVariable;

class ResultFormat
{
    /**
     * @var CommandVariable\Base[]
     */
    protected $fields;

    /**
     * Result constructor.
     *
     * @param CommandVariable\Base[] $fields
     */
    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    /**
     * Возвращает список полей результата выполнения команды
     * @return CommandVariable\Base[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $code
     *
     * @return CommandVariable\Base|null
     */
    public function getField($code)
    {
        $result = null;

        foreach ($this->fields as $field)
        {
            if($field->getCode() == $code)
            {
                $result = $field;
            }
        }

        return $result;
    }
}