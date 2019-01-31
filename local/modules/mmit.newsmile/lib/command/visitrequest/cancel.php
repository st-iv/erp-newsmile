<?

namespace Mmit\NewSmile\Command\VisitRequest;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam;
use Mmit\NewSmile\Visit\VisitRequestTable;

class Cancel extends Base
{
    protected function doExecute()
    {
        $result = VisitRequestTable::update($this->params['id'], [
            'STATUS' => 'CANCELED'
        ]);

        $this->tellAboutOrmResult($result, 'записи на приём');
    }


    public function getParamsMap()
    {
        return [
            new \Mmit\NewSmile\CommandVariable\Integer('id', 'id заявки на приём', true)
        ];
    }

}