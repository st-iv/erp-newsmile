<?

namespace Mmit\NewSmile\CommandParam;

class Regexp extends String
{
    protected $regexp;

    public function setRegexp($regexp)
    {
        $this->regexp = $regexp;
    }

    protected function formatValue($value)
    {
        $value = parent::formatValue($value);

        if(!preg_match($this->regexp, $value))
        {
            $this->sayBadValueFormat();
        }

        return $value;
    }
}