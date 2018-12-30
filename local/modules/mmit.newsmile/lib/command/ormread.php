<?php
/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 30.12.18
 * Time: 21:01
 */

namespace Mmit\NewSmile\Command;


use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Helpers;

abstract class OrmRead extends Base
{
    protected function prepareRow($row)
    {
        foreach ($row as $fieldName => &$fieldValue)
        {
            if($fieldValue instanceof DateTime)
            {
                $fieldValue = $fieldValue->format('Y-m-d H:i:s');
            }
            elseif($fieldValue instanceof Date)
            {
                $fieldValue = $fieldValue->format('Y-m-d');
            }
        }

        unset($fieldValue);

        return Helpers::camelCaseKeys($row, false);
    }

    /**
     * Возвращает Entity, с которой будет работать команда
     * @return Entity
     */
    abstract protected function getOrmEntity();
}