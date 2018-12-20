<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam\Integer;
use Mmit\NewSmile\CommandParam\String;
use Mmit\NewSmile\Notice;
use Mmit\NewSmile\Error;

class RequestChangeDate extends Base
{
    protected static $name = 'Запросить перенос приема';

    protected function doExecute()
    {
        try
        {
            $notice = new Notice\VisitChangeDate([
                'VISIT_ID' => $this->params['id'],
                'NEW_DATE' => $this->params['new_date'],
                'PATIENT_ID' => Application::getInstance()->getUser()->getId()
            ]);

            $notice->push(['admin']);
        }
        catch (Error $e)
        {
            $this->setError($e);
        }
    }

    public function getParamsMap()
    {
        return [
            new String('new_date', 'новая дата приема', '', true),
            new Integer('id', 'id приема', '', true),
        ];
    }
}