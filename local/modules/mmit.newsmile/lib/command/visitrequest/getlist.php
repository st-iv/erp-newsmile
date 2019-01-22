<?

namespace Mmit\NewSmile\Command\VisitRequest;

use Mmit\NewSmile\Command\OrmGetList;
use Mmit\NewSmile\Visit\VisitRequestTable;

class GetList extends OrmGetList
{
    protected function getOrmEntity()
    {
        return VisitRequestTable::getEntity();
    }
}