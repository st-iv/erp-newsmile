<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile\Status;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Mmit\NewSmile\Status;

Loc::loadMessages(__FILE__);

class VisitTable extends Status
{
    public static function getTableName()
    {
        return 'm_newsmile_status_visit';
    }
}
