<?

namespace Mmit\NewSmile\Command\Visit;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam\Integer;
use Mmit\NewSmile\Error;

class DetailMobile extends Base
{
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
            new Integer('id', 'id приёма', '', true)
        ];
    }
}