<?

namespace Mmit\NewSmile\CommandResult;

class Field
{
    protected $code;
    protected $description;
    protected $isOptional;
    protected $type;

    public function __construct($code, $description, $type, $isOptional = false)
    {
        $this->code = $code;
        $this->description = $description;
        $this->type = $type;
        $this->isOptional = $isOptional;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->isOptional;
    }

}