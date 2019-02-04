<?

namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\CommandVariable\ArrayParam;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\CommandVariable\Object;
use Mmit\NewSmile\Error;

class DetailMobile extends Base
{
    public function getDescription()
    {
        return 'Получает детальную нформацию о приёме с указанным id для текущего пользователя (в особом формате для мобильных приложений)';
    }

    public function getResultFormat()
    {
        $getList = new GetListMobile([], null, true);

        /**
         * @var ArrayParam $listArray
         */
        $listArray = $getList->getResultFormat()->getField('list');

        /**
         * @var Object $listObject
         */
        $listObject = $listArray->getContentType();
        $listObject->removeShapeFields(['timestamp', 'create_timestamp']);
        return new ResultFormat($listObject->getShape());
    }

    protected function doExecute()
    {
        $getListCommand = new GetListMobile([
            'ids' => [$this->params['id']]
        ]);

        $getListCommand->execute();
        $commandResult = $getListCommand->getResult();

        if($commandResult['list'])
        {
            $this->result = array_pop($getListCommand->result['list']);
            unset($this->result['timestamp']);
            unset($this->result['create_timestamp']);
        }
        else
        {
            throw new Error('Для пользователя не найдена заявка на приём с указанным id', 'VISIT_REQUEST_NOT_FOUND');
        }
    }


    public function getParamsMap()
    {
        return [
            new Integer('id', 'id приёма', true)
        ];
    }
}