<?

namespace Mmit\NewSmile\Command\VisitRequest;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Visit\VisitRequestTable;

class Cancel extends Base
{
    public function getDescription()
    {
        return 'Отменяет заявку на приём с указанным id. Пациент может отменять только свои заявки на приём.';
    }

    protected function doExecute()
    {
        $user = Application::getInstance()->getUser();

        if($user->is('patient'))
        {
            $dbVisit = VisitRequestTable::getList([
                'filter' => [
                    'PATIENT_ID' => $user->getId(),
                    'ID' => $this->params['id']
                ]
            ]);

            if(!$dbVisit->fetch())
            {
                throw new Error('Для текущего пациента не найдена заявка на приём с id=' . $this->params['id'], 'VISIT_REQUEST_NOT_FOUND');
            }
        }

        $result = VisitRequestTable::update($this->params['id'], [
            'STATUS' => 'CANCELED'
        ]);

        $this->tellAboutOrmResult($result, 'записи на приём');
    }


    public function getParamsMap()
    {
        return [
            new \Mmit\NewSmile\CommandVariable\Integer('id', 'id заявки на приём', true)
        ];
    }

}