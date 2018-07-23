<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 19.07.2018
 * Time: 16:04
 */

namespace Mmit\NewSmile;


use Bitrix\Main\GroupTable;

class Group
{
    const GROUP_ADMIN = 'GROUP_ADMIN';
    const GROUP_MARKETING = 'GROUP_MARKETING';
    const GROUP_DOCTOR = 'GROUP_DOCTOR';
    const GROUP_MANAGER = 'GROUP_MANAGER';
    const GROUP_STORAGE = 'GROUP_STORAGE';
    const GROUP_SYSTEM = 'GROUP_SYSTEM';
    const GROUP_PATIENT = 'GROUP_PATIENT';

    public static function createGroup()
    {
        $arNewGroup = [
            self::GROUP_ADMIN,
            self::GROUP_MARKETING,
            self::GROUP_DOCTOR,
            self::GROUP_MANAGER,
            self::GROUP_STORAGE,
            self::GROUP_SYSTEM,
            self::GROUP_PATIENT
        ];
        $arOldGroup = [];
        $rsGroup = GroupTable::getList([
            'filter' => ['STRING_ID' => $arNewGroup]
        ]);
        while ($arGroup = $rsGroup->fetch()) {
            $arOldGroup[$arGroup['ID']] = $arGroup['STRING_ID'];
        }

        foreach ($arNewGroup as $group)
        {
            if (in_array($group, $arOldGroup)) {
                GroupTable::update(
                    array_search($group, $arOldGroup),
                    [
                        'ACTIVE' => 'Y',
                        'C_SORT' => '100',
                        'ANONYMOUS' => 'N',
                        'NAME' => $group,
                        'DESCRIPTION' => '',
                        'STRING_ID' => $group
                    ]
                );
            } else {
                GroupTable::add(
                    [
                        'ACTIVE' => 'Y',
                        'C_SORT' => '100',
                        'ANONYMOUS' => 'N',
                        'NAME' => $group,
                        'DESCRIPTION' => '',
                        'STRING_ID' => $group
                    ]
                );
            }
        }
    }

    public static function inGroup($codeGroup)
    {
        $rsGroup = GroupTable::getList([
            'filter' => ['STRING_ID' => $codeGroup]
        ]);
        if ($arGroup = $rsGroup->fetch()) {
            global $USER;
            if (in_array($arGroup['ID'], $USER->GetUserGroupArray())) {
                return true;
            }
            else {
                return false;
            }
        }
        return false;
    }
}