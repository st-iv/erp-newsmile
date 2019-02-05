<?


namespace Mmit\NewSmile\Command\Notice;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\OrmEntityDelete;
use Mmit\NewSmile\Notice\Data\NoticeTable;

class Delete extends OrmEntityDelete
{
    public function getDescription()
    {
        return 'Удаляет уведомления текущего пользователя';
    }

    public function getOrmEntity()
    {
        return NoticeTable::getEntity();
    }

    protected function getFilter()
    {
        return [
            'USER_ID' => Application::getInstance()->getUser()->getBitrixId()
        ];
    }
}