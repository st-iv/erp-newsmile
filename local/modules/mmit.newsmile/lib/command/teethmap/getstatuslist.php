<?


namespace Mmit\NewSmile\Command\TeethMap;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\Status\ToothTable;
use Mmit\NewSmile\CommandVariable;

class GetStatusList extends Base
{
    public function getDescription()
    {
        return 'Получает список статусов зубов';
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            (new CommandVariable\ArrayParam('status_list', 'полный список статусов зубов', true))->setContentType(
                (new CommandVariable\Object('', ''))->setShape([
                    new CommandVariable\Integer('id', 'id статуса', true),
                    new CommandVariable\String('code', 'код статуса', true),
                    new CommandVariable\String('decode', 'расшифровка статуса', true),
                ])
            ),
        ]);
    }

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