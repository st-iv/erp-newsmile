<?


namespace Mmit\NewSmile\Command\Service;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile;

class GetList extends Base
{
    protected static $name = 'Получить список услуг';

    protected function doExecute()
    {
        $tree = NewSmile\Service\ServiceTable::get1LvlTree();

        foreach ($tree as $firstLvlGroup)
        {
            foreach ($firstLvlGroup['SERVICES'] as &$service)
            {
                unset($service['GROUP_ID']);
                $service = NewSmile\Helpers::strtolowerKeys($service);
            }

            $firstLvlGroup['service_list'] = $firstLvlGroup['SERVICES'];
            unset($firstLvlGroup['GROUP_ID']);
            unset($firstLvlGroup['SUBGROUPS']);
            unset($firstLvlGroup['SERVICES']);
            unset($service);

            $this->result['category_list'][] = NewSmile\Helpers::strtolowerKeys($firstLvlGroup);
        }
    }

    protected function getServicesRecursive($group, $allServices)
    {
        $result = $allServices[$group['ID']];

        foreach ($group['SUBGROUPS'] as $subgroup)
        {
            $result = array_merge($result, $this->getServicesRecursive($subgroup, $allServices));
        }

        return $result;
    }

    public function getParamsMap()
    {
        return [];
    }
}