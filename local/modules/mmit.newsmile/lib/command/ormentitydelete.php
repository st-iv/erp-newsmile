<?

namespace Mmit\NewSmile\Command;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\Field;
use Mmit\NewSmile\CommandVariable;
use Mmit\NewSmile\Helpers;

abstract class OrmEntityDelete extends OrmEntityWrite
{
    protected function doExecute()
    {
        $primaries = $this->params['primaries'];
        foreach ($primaries as &$primary)
        {
            if(is_array($primary))
            {
                $primary = Helpers::snakeCaseKeys($primary);
            }
        }

        unset($primary);

        /**
         * @var DataManager $dataManager
         */
        $dataManager = $this->getOrmEntity()->getDataClass();

        $filter = $this->getFilter();

        if($filter)
        {
            $primaries = array_filter($this->params['primaries'], function($primary) use ($dataManager, $filter)
            {
                $dbItems = $dataManager::getByPrimary($primary, [
                    'filter' => $filter,
                    'select' => []
                ]);

                return ($dbItems->fetch() == true);
            });
        }

        foreach ($primaries as $primary)
        {
            $deleteResult = $dataManager::delete($primary);
            $this->tellAboutOrmResult($deleteResult);
        }
    }

    public function getParamsMap()
    {

        $ormEntity = $this->getOrmEntity();
        $primary = $ormEntity->getPrimary();

        /*
         * Формируем правильный формат параметра в зависимости от того, является ли первичный ключ составным
         */
        if(is_array($primary))
        {
            $primaryObjectShape = [];
            $primariesArrayDescription = 'первичные ключи удаляемых записей';

            foreach ($primary as $primaryFieldName)
            {
                $primaryObjectShape[] = $this->getParamByField($ormEntity->getField($primaryFieldName));
            }

            $primariesItemType = new CommandVariable\Object('', '');
            $primariesItemType->setShape($primaryObjectShape);

        }
        else
        {
            $primaryField = $ormEntity->getField($primary);
            $primariesItemType = $this->getParamByField($primaryField);
            $primariesArrayDescription = 'список ' . $primaryField->getTitle() . ' удаляемых записей';
        }

        $primariesArray = new CommandVariable\ArrayParam('primaries', $primariesArrayDescription, true);
        $primariesArray->setContentType($primariesItemType);


        return [$primariesArray];
    }

    protected function filterField(Field $field)
    {
        return true;
    }

    /**
     * Возвращает фильтр записей для удаления. Будут удалены только те записи, которые попадают под данный фильтр.
     * @return array
     */
    abstract protected function getFilter();
}