<?

namespace Mmit\NewSmile\Command\TableField;

use Mmit\NewSmile\Command\Base;

class GetList extends Base
{
    // TODO понять, как ограничить доступ к этой команде, чтобы не светить структуру бд всем подряд. Возможно вообще запретить её для
    // контроллера rest?
    protected function doExecute()
    {
        //$dataManagerClass
    }

    public function getParamsMap()
    {
        return [
            'dataManager' => [
                'TITLE' => 'data manager',
                'DESCRIPTION' => 'Класс data manager сущности, по полям которой необходимо получить информацию (относительно неймспейса модуля)',
                'REQUIRED' => true
            ]
        ];
    }
}