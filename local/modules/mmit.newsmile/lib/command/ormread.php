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

abstract class OrmRead extends Base implements OrmCommand
{
    protected function prepareRow($row)
    {
        $row = $this->doPrepareRow($row);

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
     * Подготавливает массив строки таблицы перед преобразованием дат и ключей массива
     * @param array $row
     *
     * @return array
     */
    protected function doPrepareRow(array $row)
    {
        return $row;
    }
}