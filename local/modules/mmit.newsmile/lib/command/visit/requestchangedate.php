<?


namespace Mmit\NewSmile\Command\Visit;

use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandVariable\String;
use Mmit\NewSmile\Notice;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Visit\ChangeDateRequestTable;

class RequestChangeDate extends Base
{
    protected static $name = 'Запросить перенос приема';

    protected function doExecute()
    {
        $visitId = (int)$this->params['id'];

        /* добавление / обновление заявки на перенос даты */

        $changeDateRequest = ChangeDateRequestTable::getByPrimary([
            'VISIT_ID' => $visitId
        ])->fetch();

        $newDate = $this->params['new_date'] ? new DateTime($this->params['new_date'], 'Y-m-d H:i:s') : null;

        if($changeDateRequest)
        {
            $result = ChangeDateRequestTable::update(['VISIT_ID' => $visitId], [
                'NEW_DATE' => $newDate
            ]);
        }
        else
        {
            $result = ChangeDateRequestTable::add([
                'VISIT_ID' => $visitId,
                'NEW_DATE' => $newDate
            ]);
        }

        if($result->isSuccess())
        {
            /* отправка уведомления */

            $notice = new Notice\VisitChangeDate([
                'VISIT_ID' => $visitId,
                'NEW_DATE' => $this->params['new_date'] ?: '(дата не указана)',
                'PATIENT_ID' => Application::getInstance()->getUser()->getId()
            ]);

            $notice->push(['admin']);
        }
        else
        {
            throw new Error('Ошибка добавления заявки на перенос даты приёма: ' . implode('; ', $result->getErrorMessages()), 'CHANGE_DATE_REQUEST_ADD_ERROR');
        }
    }

    public function getParamsMap()
    {
        return [
            new \Mmit\NewSmile\CommandVariable\DateTime('new_date', 'новая дата приема'),
            new String('id', 'id приема', true),
        ];
    }
}