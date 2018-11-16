<?


namespace Mmit\NewSmile;

use Throwable;

class Error extends \Exception
{
    protected $charCode;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->charCode = $code;
        parent::__construct($message, 0, $previous);
    }

    public function getCharCode()
    {
        return $this->charCode;
    }
}