<?


namespace Mmit\NewSmile\CommandVariable;

use Mmit\NewSmile\CommandVariable\Regexp;

class Time extends Regexp
{
    protected static $timeRegexpBody = '[0-9]{2}:[0-9]{2}(:[0-9]{2})?';

    protected function init()
    {
        $this->setRegexp('/^' . static::$timeRegexpBody . '$/');
    }

    public static function getRegexpBody()
    {
        return static::$timeRegexpBody;
    }

    public function getTypeName()
    {
        return 'время';
    }

    public function getTypeNameGenitive()
    {
        return 'времени';
    }
}