<?

namespace Mmit\NewSmile\Scenario;

abstract class Scenario
{
    protected $status;

    public function setStatus(Status $status)
    {
        $this->status = $status;
    }

    //abstract protected function init();
}