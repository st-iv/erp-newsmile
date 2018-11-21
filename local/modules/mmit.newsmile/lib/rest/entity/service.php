<?

namespace Mmit\NewSmile\Rest\Entity;

use Mmit\NewSmile\Helpers;
use Mmit\NewSmile;

class Service extends Controller
{
    protected function processList()
    {
        $tree = NewSmile\Service\ServiceTable::get1LvlTree();

        foreach ($tree as $firstLvlGroup)
        {
            foreach ($firstLvlGroup['SERVICES'] as &$service)
            {
                unset($service['GROUP_ID']);
                $service = Helpers::strtolowerKeys($service);
            }

            $firstLvlGroup['service_list'] = $firstLvlGroup['SERVICES'];
            unset($firstLvlGroup['GROUP_ID']);
            unset($firstLvlGroup['SUBGROUPS']);
            unset($firstLvlGroup['SERVICES']);
            unset($service);

            $this->responseData['category_list'][] = Helpers::strtolowerKeys($firstLvlGroup);
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

    protected function getActionsMap()
    {

        return [
            'list' => []
        ];
    }
}