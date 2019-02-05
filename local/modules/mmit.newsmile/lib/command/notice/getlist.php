<?


namespace Mmit\NewSmile\Command\Notice;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\OrmGetList;
use Mmit\NewSmile\Notice\Data\NoticeTable;

class GetList extends OrmGetList
{
    public function getDescription()
    {
        return 'Возвращает список уведомлений для текущего пользователя';
    }

    public function getOrmEntity()
    {
        return NoticeTable::getEntity();
    }

    protected function doPrepareRow(array $row)
    {
        NoticeTable::extendNoticeDataByType($row);
        return $row;
    }

    protected function modifyFilter(array $filter)
    {
        // для всех ролей доступен запрос только своих уведомлений
        $filter['USER_ID'] = Application::getInstance()->getUser()->getBitrixId();
        return $filter;
    }
}