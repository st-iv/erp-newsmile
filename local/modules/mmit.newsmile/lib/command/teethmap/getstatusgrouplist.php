<?

namespace Mmit\NewSmile\Command\TeethMap;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Status\ToothTable;

class GetStatusGroupList extends Base
{
    protected static $name = 'Получить список групп статусов зубов';

    protected function doExecute()
    {
        $groups = ToothTable::getEnumVariants('GROUP');

        foreach ($groups as $code => $name)
        {
            $this->result['status_group_list'][] = [
                'code' => $code,
                'decode' => $name,
            ];
        }
    }

    public function getParamsMap()
    {
        return [];
    }
}