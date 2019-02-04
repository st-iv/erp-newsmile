<?

namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\CommandVariable;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Visit\VisitTable;

class Cancel extends Base
{
    public function getDescription()
    {
        return 'Отменяет приём с указанным id. Для пациента возможна отмена только своих приёмов.';
    }

    public function getResultFormat()
    {
        return new ResultFormat([]);
    }

    protected function doExecute()
    {
        $user = Application::getInstance()->getUser();

        if($user->is('patient'))
        {
            $dbVisit = VisitTable::getList([
                'filter' => [
                    'PATIENT_ID' => $user->getId(),
                    'ID' => $this->params['id']
                ]
            ]);

            if(!$dbVisit->fetch())
            {
                throw new Error('Для текущего пациента не найден приём с id=' . $this->params['id'], 'VISIT_NOT_FOUND');
            }
        }

        $result = VisitTable::update($this->params['id'], [
            'STATUS' => 'CANCELED'
        ]);

        $this->tellAboutOrmResult($result, 'статуса приёма');
    }

    public function getParamsMap()
    {
        return [
            new \Mmit\NewSmile\CommandVariable\Integer('id', 'id приёма', true)
        ];
    }
}