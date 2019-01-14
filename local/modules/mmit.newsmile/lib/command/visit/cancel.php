<?

namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam;
use Mmit\NewSmile\Visit\VisitTable;

class Cancel extends Base
{
    protected function doExecute()
    {
        $result = VisitTable::update($this->params['id'], [
            'STATUS' => 'CANCELED'
        ]);

        $this->tellAboutOrmResult($result, 'статуса приёма');
    }

    public function getParamsMap()
    {
        return [
            new CommandParam\Integer('id', 'id приёма', '', true)
        ];
    }
}