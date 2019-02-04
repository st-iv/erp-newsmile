<?

namespace Mmit\NewSmile\Command\TeethMap;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\Status\ToothTable;
use Mmit\NewSmile\CommandVariable;

class GetStatusGroupList extends Base
{
    public function getDescription()
    {
        return 'Получает список групп статусов зубов';
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            (new CommandVariable\ArrayParam('status_group_list', 'список групп статусов', true))->setContentType(
                (new CommandVariable\Object('', ''))->setShape([
                    new CommandVariable\String('code', 'код группы статусов', true),
                    new CommandVariable\String('decode', 'расшифровка группы статусов', true),
                ])
            )
        ]);
    }

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