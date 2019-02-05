<?

namespace Mmit\NewSmile\Command\VisitRequest;

use Mmit\NewSmile\Command\OrmGetList;
use Mmit\NewSmile\Visit\VisitRequestTable;

class GetList extends OrmGetList
{
    public function getDescription()
    {
        return 'Возвращает список заявок на приём.';
    }

    public function getOrmEntity()
    {
        return VisitRequestTable::getEntity();
    }
}