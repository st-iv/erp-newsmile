<?

namespace Mmit\NewSmile\CommandVariable;

class Regexp extends String
{
    protected $regexp;

    public function setRegexp($regexp)
    {
        $this->regexp = $regexp;
    }

    public function formatValue($value)
    {
        $value = parent::formatValue($value);

        if(!preg_match($this->regexp, $value))
        {
            $this->sayBadValueFormat();
        }

        return $value;
    }
}