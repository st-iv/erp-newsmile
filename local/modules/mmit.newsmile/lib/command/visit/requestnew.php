<?

namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam\Bool;
use Mmit\NewSmile\CommandParam\Integer;
use Mmit\NewSmile\CommandParam\String;
use Mmit\NewSmile\Notice;
use Mmit\NewSmile\Application;

class RequestNew extends Base
{
    protected static $name = 'Запросить запись на прием';

    protected function doExecute()
    {
        try
        {
            $notice = new Notice\NewVisitRequest([
                'SERVICE_ID' => $this->params['service_id'],
                'DATE' => $this->params['date'],
                'NEAR_FUTURE' => $this->params['near_future'] === 'true',
                'COMMENT' => $this->params['comment'],
                'PATIENT_ID' => Application::getInstance()->getUser()->getId()
            ]);

            $notice->push(['admin']);
        }
        catch(\Error $e)
        {
            $this->setError($e);
        }
    }

    public function getParamsMap()
    {
        return [
            new Integer('service_id', 'id услуги'),
            new String('date', 'желаемая дата приема'),
            new Bool('near_future', 'флаг записи на ближайшее время'),
            new String('comment', 'комментарий')
        ];
    }
}