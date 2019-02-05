<?


namespace Mmit\NewSmile\Command\Notice;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Notice;

class GetGroupList extends Base
{
    public function getDescription()
    {
        return 'Получает список групп уведомлений';
    }

    protected function doExecute()
    {
        $groups = Notice\Data\TypeTable::getEnumVariants('GROUP');
        $this->result['list'] = [];

        foreach ($groups as $groupCode => $groupName)
        {
            $this->result['list'][] = [
                'code' => $groupCode,
                'name' => $groupName
            ];
        }
    }

    public function getParamsMap()
    {
        return [];
    }

}