<?

namespace Mmit\NewSmile\Command;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\CommandParam;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;

abstract class OrmGetList extends OrmRead
{
    // TODO нужно обязать дочерние классы детально описывать схему доступа к полям сущности

    protected function doExecute()
    {
        $entity = $this->getOrmEntity();
        $dataManagerClass = $entity->getDataClass();

        $queryParams = $this->params;
        $queryParams['count_total'] = $queryParams['countTotal'];
        unset($queryParams['countTotal']);

        $dbRows = $dataManagerClass::getList($queryParams);

        $this->result = [];

        while($row = $dbRows->fetch())
        {
            $this->result['list'][] = $this->prepareRow($row);

            if($queryParams['count_total'])
            {
                $this->result['count'] = $dbRows->getCount();
            }
        }
    }

    protected function prepareParamValue($paramCode, $paramValue)
    {
        if(($paramCode == 'filter') || ($paramCode == 'order'))
        {
            $paramValue = $this->prepareFieldsArray($paramValue, true);
        }
        elseif($paramCode == 'select')
        {
            $paramValue = $this->prepareFieldsArray($paramValue);

            /* блокировка использования точки в select, тк этот механизм позволяет обойти систему контроля доступа */
            foreach ($paramValue as $item)
            {
                if(!preg_match('/^[A-Za-z0-9_]+$/', $item))
                {
                    throw new Error('Поля сущностей по ссылкам в select не поддерживаются', 'SELECT_LINKED_ENTITIES_NOT_SUPPORTED');
                }
            }
        }

        return $paramValue;
    }


    protected function prepareFieldsArray($array, $bKeys = false)
    {
        $entity = $this->getOrmEntity();
        $result = [];

        foreach ($array as $key => $value)
        {
            $rawFieldCode = ($bKeys ? $key : $value);
            $fieldCode = Helpers::getSnakeCase($rawFieldCode);

            if($entity->hasField($fieldCode))
            {
                if($bKeys)
                {
                    $result[$fieldCode] = $value;
                }
                else
                {
                    $result[] = $fieldCode;
                }
            }
            else
            {
                $this->sayBadFieldCode($rawFieldCode);
            }
        }

        return $result;
    }

    protected function sayBadFieldCode($fieldCode)
    {
        throw new Error('Поле с кодом ' . $fieldCode . ' не существует', 'BAD_FIELD_CODE');
    }

    public function getParamsMap()
    {
        return [
            new CommandParam\ArrayParam(
                'filter',
                'фильтр',
                    'объект со значениями полей для фильтрации'
            ),
            new CommandParam\ArrayParam(
                'select',
                'поля для выборки',
                'массив кодов полей для выборки'
            ),
            new CommandParam\ArrayParam(
                'order',
                'порядок сортировки',
                'Объект, содержащий в качестве ключей имена полей, а в качестве значений - направление сортировки'
            ),
            new CommandParam\Integer('limit', 'ограничение по количеству'),
            new CommandParam\Bool(
                'countTotal',
                'флаг подсчета количества',
                'Если установлен, вернёт общее количество записей по ключу count',
                false,
                false
            )
        ];
    }
}