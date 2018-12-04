<?


namespace Mmit\NewSmile\Command\TeethMap;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Status\ToothTable;

class GetStatusList extends Base
{
    protected static $name = 'Получить список статусов зубов';

    protected function doExecute()
    {
        $dbToothStatuses = ToothTable::getList();

        while($status = $dbToothStatuses->fetch())
        {
            $this->result['status_list'][] = [
                'id' => $status['ID'],
                'code' => $status['CODE'],
                'decode' => $status['NAME']
            ];
        }
    }

    public function getParamsMap()
    {
        return [];
    }
}