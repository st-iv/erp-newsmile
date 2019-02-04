<?


namespace Mmit\NewSmile\Command\Service;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile;
use Mmit\NewSmile\CommandVariable;

class GetList extends Base
{
    public function getDescription()
    {
        return 'Получает список услуг в особом формате для мобильных приложений';
    }

    public function getResultFormat()
    {
        return new NewSmile\Command\ResultFormat([
            (new CommandVariable\ArrayParam('category_list', 'список групп услуг', true))->setContentType(
                (new CommandVariable\Object('', ''))->setShape([
                    new CommandVariable\Integer('id', 'id группы услуг', true),
                    new CommandVariable\String('name', 'название группы услуг', true),
                    (new CommandVariable\ArrayParam('service_list', 'список услуг', true))->setContentType(
                        (new CommandVariable\Object('', '', true))->setShape([
                            new CommandVariable\Integer('id', 'id услуги', true),
                            new CommandVariable\String('name', 'название услуги', true)
                        ])
                    )
                ])
            )
        ]);
    }

    protected function doExecute()
    {
        $tree = NewSmile\Service\ServiceTable::get1LvlTree();

        $this->result['category_list'] = [];

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