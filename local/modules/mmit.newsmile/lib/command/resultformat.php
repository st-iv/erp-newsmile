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
}