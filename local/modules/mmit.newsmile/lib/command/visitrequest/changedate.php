<?


namespace Mmit\NewSmile\Command\VisitRequest;

use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Notice;
use Mmit\NewSmile\Visit\VisitRequestTable;
use Mmit\NewSmile\CommandParam;

class ChangeDate extends Base
{
    protected function doExecute()
    {
        $visitRequest = VisitRequestTable::getByPrimary($this->params['id'], [
            'select' => ['DATE', 'STATUS']
        ])->fetch();

        if(!$visitRequest)
        {
            throw new Error('Не найдена заявка на приём с указанным id: ' . $this->params['id'], 'VISIT_REQUEST_NOT_FOUND');
        }

        if($visitRequest['STATUS'] != 'WAITING')
        {
            throw new Error(
                sprintf('Нельзя изменить заявку на приём не в статусе ожидания рассмотрения (актуальный статус %s)', $visitRequest['STATUS']),
                'VISIT_REQUEST_CHANGE_NOT_ALLOWED'
            );
        }

        $oldDate = $visitRequest['DATE'] ? $visitRequest['DATE']->format('Y-m-d H:i:s') : null;
        $newDate = $this->params['new_date'] ? new DateTime($this->params['new_date'], 'Y-m-d H:i:s') : null;

        if($oldDate == $this->params['new_date']) return;

        $updateResult = VisitRequestTable::update($this->params['id'], [
            'DATE' => $newDate
        ]);

        if($updateResult->isSuccess())
        {
            $notice = new Notice\VisitRequestChangeDate([
                'PATIENT_ID' => Application::getInstance()->getUser()->getId(),
                'NEW_DATE' => $this->params['new_date'] ?: '(время не указано)',
                'OLD_DATE' => $oldDate ?: '(время не указано)'
            ]);

            $notice->push(['admin']);
        }
    }

    public function getParamsMap()
    {
        return [
            new CommandParam\DateTime('new_date', 'новая дата заявки на приём', ''),
            new CommandParam\String('id', 'id заявки на приём', '', true),
        ];
    }
}