<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 27.09.2018
 * Time: 14:04
 */

namespace Mmit\NewSmile;

use Bitrix\Main\Diag\Debug;

class Ajax
{
    protected static $areasStack = array();
    protected static $requestedArea = '';
    protected static $result = array();

    public static function start($areaId, $bWriteContent = true, $bShowAreaId = true)
    {
        global $APPLICATION;

        static::addArea($areaId, $bWriteContent, $bShowAreaId);

        if($bShowAreaId)
        {
            echo '<div ' . static::getAreaIdAttr() . '>';
        }

        if(static::isAreaRequested() && $bWriteContent)
        {
            $APPLICATION->RestartBuffer();
            ob_start();
        }

        if(static::isCurrentAreaRequested())
        {
            static::$requestedArea = $areaId;
        }
    }

    public static function finish()
    {
        if(static::isAreaRequested())
        {
            $isCurrentAreaRequested = static::isCurrentAreaRequested();
            $currentArea = array_pop(static::$areasStack);

            if($currentArea['WRITE_CONTENT'])
            {
                static::$result['content'][$currentArea['AREA_ID']] = ob_get_clean();
            }

            foreach ($currentArea as $paramName => $paramValue)
            {
                if(($paramName != 'SHOW_AREA_ID') && ($paramName != 'AREA_ID') && ($paramName != 'WRITE_CONTENT'))
                {
                    static::$result[$paramName] = $paramValue;
                }
            }

            if($isCurrentAreaRequested)
            {
                global $APPLICATION;
                $APPLICATION->RestartBuffer();
                static::$result['success'] = true;
                die(json_encode(static::$result));
            }
        }
        else
        {
            $currentArea = array_pop(static::$areasStack);
            if($currentArea['SHOW_AREA_ID'])
            {
                echo '</div>';
            }
        }
    }

    public static function isAjaxQuery()
    {
        return (($_REQUEST['ajax'] == 'Y') && !empty($_REQUEST['area']));
    }

    protected static function isCurrentAreaRequested()
    {
        return (static::isAjaxQuery() && ($_REQUEST['area'] == static::getAreaParam('AREA_ID')));
    }

    protected static function isParentAreaRequested()
    {
        return !empty(static::$requestedArea);
    }

    public static function isAreaRequested()
    {
        return (static::isParentAreaRequested() || static::isCurrentAreaRequested());
    }

    protected static function addArea($areaId, $bWriteContent, $bShowAreaId)
    {
        static::$areasStack[] = array(
            'AREA_ID' => $areaId,
            'SHOW_AREA_ID' => $bShowAreaId,
            'WRITE_CONTENT' => $bWriteContent,
        );
    }

    protected static function getAreaParam($paramName)
    {
        return static::$areasStack[count(static::$areasStack) - 1][$paramName];
    }

    public static function setAreaParam($paramName, $paramValue)
    {
        Debug::writeToFile('setAreaParam!!');
        Debug::writeToFile($paramName);
        Debug::writeToFile($paramValue);
        static::$areasStack[count(static::$areasStack) - 1][$paramName] = $paramValue;
        Debug::writeToFile(static::$areasStack[count(static::$areasStack) - 1]);
        Debug::writeToFile(static::$areasStack);
    }

    public static function getAreaIdAttr()
    {
        return 'data-is-ajax-area="Y" data-ajax-area="' . static::getAreaParam('AREA_ID') . '"';
    }
}