<?php

namespace Mmit\NewSmile\Sms;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class TokenTable extends Entity\DataManager
{
    const LENGTH_TOKEN = 16;

    public static function getTableName()
    {
        return 'm_newsmile_token';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            ]),
            new Entity\StringField('VALUE', [
                'title' => 'VALUE TOKEN',
                'required' => true,
            ]),
            new Entity\IntegerField('USER_ID', [
                'title' => 'USER_ID',
                'required' => true,
            ]),
            new Entity\ReferenceField('USER',
                'Bitrix\Main\User',
                array('=this.USER_ID' => 'ref.ID'),
                array(
                    'title' => 'Пользователь'
                )
            ),
            new Entity\DatetimeField('DATE_CREATE', [
                'title' => 'DATE_CREATE',
                'default_value' => function () {
                    return new DateTime();
                }
            ])
        ];
    }

    private static function generateToken($length)
    {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

    public static function getToken($userId)
    {
        $rsResult = self::getList([
            'filter' => [
               'USER_ID' => $userId
            ]
        ]);
        if ($arResult = $rsResult->fetch())
        {
            return $arResult['VALUE'];
        }
        else
        {
            $arFields = [
                'VALUE' => self::generateToken(self::LENGTH_TOKEN),
                'USER_ID' => $userId
            ];

            $res = self::add($arFields);

            if ($res->isSuccess())
            {
                return $arFields['VALUE'];
            }
            else
            {
                return false;
            }
        }
    }

    public static function getUserByToken($token) {
        $arFilter = [
            'VALUE' => $token
        ];
        $arSelect = [
            'USER_ID'
        ];
        $rsResult = self::getList([
            'filter' => $arFilter,
            'select' => $arSelect
        ]);
        if ($arResult = $rsResult->fetch()) {
            return $arResult['USER_ID'];
        } else {
            return false;
        }
    }
}