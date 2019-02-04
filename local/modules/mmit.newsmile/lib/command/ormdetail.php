<?

namespace Mmit\NewSmile\Command;


use Bitrix\Main\ORM\Entity;
use Mmit\NewSmile\CommandVariable\Any;
use Mmit\NewSmile\Helpers;

abstract class OrmDetail extends OrmRead
{
    protected function doExecute()
    {
        $entity = $this->getOrmEntity();
        $dataManagerClass = $entity->getDataClass();

        $queryParams = [];
        if($this->params['select'])
        {
            $queryParams['select'] = $this->params['select'];
        }

        $dbRow = $dataManagerClass::getByPrimary($this->params['primary'], [
            'select' => $queryParams['select']
        ]);

        if($row = $dbRow->fetch())
        {
            $this->result = $this->prepareRow($row);
        }
    }

    public function getParamsMap()
    {
        return [
            new Any('primary', 'первичный ключ', true
            ),
            OrmGetList::getParam('select')
        ];
    }
}