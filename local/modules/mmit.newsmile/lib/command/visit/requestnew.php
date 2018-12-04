<?

namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base;
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
            'service_id' => [
                'TITLE' => 'id услуги',
                'REQUIRED' => false
            ],
            'date' => [
                'TITLE' => 'желаемая дата приема',
                'REQUIRED' => false
            ],
            'near_future' => [
                'TITLE' => 'флаг записи на ближайшее время',
                'REQUIRED' => false
            ],
            'comment' => [
                'TITLE' => 'комментарий',
                'REQUIRED' => false
            ]
        ];
    }
}