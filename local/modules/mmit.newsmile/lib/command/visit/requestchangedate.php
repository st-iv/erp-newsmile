<?


namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Notice;
use Mmit\NewSmile\Error;

class RequestChangeDate extends Base
{
    public function execute()
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
            'new_date' => [
                'TITLE' => 'новая дата приема',
                'REQUIRED' => true
            ],
            'id' => [
                'TITLE' => 'id приема',
                'REQUIRED' => true
            ]
        ];
    }

    public function getName()
    {
        return 'Запросить перенос приема';
    }
}