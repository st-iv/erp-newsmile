<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<form action="" class="patient-edit" method="post">
    <table>
        <tr>
            <td>
                <lable>Статус</lable>
            </td>
            <td>
                <select name="STATUS_ID" id="">
                    <?foreach ($arResult['STATUS_PATIENT'] as $arStatus):?>
                        <option value="<?=$arStatus['ID']?>" <?=($arStatus['ID'] = $arResult['PATIENT_CARD']['ID'])? 'selected':'';?>><?=$arStatus['NAME']?></option>
                    <?endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Номер карты</lable>
            </td>
            <td>
                <input type="text" name="NUMBER" value="<?=$arResult['PATIENT_CARD']['NUMBER']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Начальная сумма лечения</lable>
            </td>
            <td>
                <input type="text" name="FIRST_PRICE" value="<?=$arResult['PATIENT_CARD']['FIRST_PRICE']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Первый прием</lable>
            </td>
            <td>
                <input type="datetime-local" name="FIRST_VISIT" value="<?=$arResult['PATIENT_CARD']['FIRST_VISIT'];?>">
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Фамилия</lable>
            </td>
            <td>
                <input type="text" name="LAST_NAME" value="<?=$arResult['PATIENT_CARD']['LAST_NAME']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Имя</lable>
            </td>
            <td>
                <input type="text" name="NAME" value="<?=$arResult['PATIENT_CARD']['NAME']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Отчество</lable>
            </td>
            <td>
                <input type="text" name="SECOND_NAME" value="<?=$arResult['PATIENT_CARD']['SECOND_NAME']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Дата рождения</lable>
            </td>
            <td>
                <input type="date" name="PERSONAL_BIRTHDAY" value="<?=$arResult['PATIENT_CARD']['PERSONAL_BIRTHDAY']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Пол</lable>
            </td>
            <td>
                <input type="radio" name="PERSONAL_GENDER" value="M" <?=($arResult['PATIENT_CARD']['PERSONAL_GENDER'] == 'M' ) ? 'checked': '';?>><lable>Мужской</lable>
                <input type="radio" name="PERSONAL_GENDER" value="F" <?=($arResult['PATIENT_CARD']['PERSONAL_GENDER'] == 'F' ) ? 'checked': '';?>><lable>Женский</lable>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Представитель</lable>
            </td>
            <td>
                <input type="text" name="REPRESENTATIVE" value="<?=$arResult['PATIENT_CARD']['REPRESENTATIVE']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Родитель</lable>
            </td>
            <td>
                <input type="text" name="PARENTS" value="<?=$arResult['PATIENT_CARD']['PARENTS']?>">
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Телефон</lable>
            </td>
            <td>
                <input type="tel" name="PERSONAL_PHONE" value="<?=$arResult['PATIENT_CARD']['PERSONAL_PHONE']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Дополнительный телефон</lable>
            </td>
            <td>
                <input type="tel" name="PERSONAL_MOBILE" value="<?=$arResult['PATIENT_CARD']['PERSONAL_MOBILE']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>СМС рассылка</lable>
            </td>
            <td>
                <input type="checkbox" name="SMS_NOTICE" value="1" <?=($arResult['PATIENT_CARD']['SMS_NOTICE'] == 1) ? 'checked' : '';?>>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Email</lable>
            </td>
            <td>
                <input type="email" name="EMAIL" value="<?=$arResult['PATIENT_CARD']['EMAIL']?>">
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Город</lable>
            </td>
            <td>
                <input type="text" name="PERSONAL_CITY" value="<?=$arResult['PATIENT_CARD']['PERSONAL_CITY']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Почтовый индекс</lable>
            </td>
            <td>
                <input type="text" name="PERSONAL_ZIP" value="<?=$arResult['PATIENT_CARD']['PERSONAL_ZIP']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Улица дом</lable>
            </td>
            <td>
                <textarea name="PERSONAL_STREET" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['PERSONAL_STREET']?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Дополнительные заметки</lable>
            </td>
            <td>
                <textarea name="PERSONAL_NOTES" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['PERSONAL_NOTES']?></textarea>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Комментарий</lable>
            </td>
            <td>
                <textarea name="COMMENT" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['COMMENT']?></textarea>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Лечащие врачи</lable>
            </td>
            <td>
                <select name="DOCTORS_ID[]" id="" multiple>
                    <?foreach ($arResult['DOCTORS'] as $arDoctor):?>
                        <option value="<?=$arDoctor['ID']?>" <?=(in_array($arDoctor['ID'],$arResult['PATIENT_CARD']['DOCTORS_ID'])) ? 'selected' : '';?>><?=$arDoctor['NAME']?></option>
                    <?endforeach;?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Обязателен чек</lable>
            </td>
            <td>
                <input type="checkbox" name="NEED_CHECK" value="1" <?=($arResult['PATIENT_CARD']['NEED_CHECK'] == 1) ? 'checked' : '';?>>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Место работы</lable>
            </td>
            <td>
                <input type="text" name="WORK_COMPANY" value="<?=$arResult['PATIENT_CARD']['WORK_COMPANY']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Профессия</lable>
            </td>
            <td>
                <input type="text" name="WORK_POSITION" value="<?=$arResult['PATIENT_CARD']['WORK_POSITION']?>">
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Серия и номер</lable>
            </td>
            <td>
                <input type="text" name="PASSPORT_SN" value="<?=$arResult['PATIENT_CARD']['PASSPORT_SN']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Кем выдан</lable>
            </td>
            <td>
                <textarea name="PASSPORT_ISSUED_BY" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['PASSPORT_ISSUED_BY']?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Дата выдачи</lable>
            </td>
            <td>
                <input type="date" name="PASSPORT_ISSUED_DATE" value="<?=$arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Место рождения</lable>
            </td>
            <td>
                <input type="text" name="PASSPORT_PLACE_BIRTH" value="<?=$arResult['PATIENT_CARD']['PASSPORT_PLACE_BIRTH']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Адрес регистрации</lable>
            </td>
            <td>
                <textarea name="PASSPORT_ADDRESS" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['PASSPORT_ADDRESS']?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Дата регистрации</lable>
            </td>
            <td>
                <input type="date" name="PASSPORT_ADDRESS_DATE" value="<?=$arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']?>">
            </td>
        </tr>
        <tr>
            <td>
                <lable>Другой документ</lable>
            </td>
            <td>
                <textarea name="PASSPORT_OTHER" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['PASSPORT_OTHER']?></textarea>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Источник</lable>
            </td>
            <td>
                <textarea name="SOURCE" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['SOURCE']?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Архив</lable>
            </td>
            <td>
                <select name="ARCHIVE" id="">
                    <option value="0" <?=($arResult['PATIENT_CARD']['ARCHIVE'] == 0) ? 'selected' : '';?>>Нет</option>
                    <option value="1" <?=($arResult['PATIENT_CARD']['ARCHIVE'] == 1) ? 'selected' : '';?>>Недовольство качеством</option>
                    <option value="2" <?=($arResult['PATIENT_CARD']['ARCHIVE'] == 2) ? 'selected' : '';?>>Недовольство ценой</option>
                    <option value="3" <?=($arResult['PATIENT_CARD']['ARCHIVE'] == 3) ? 'selected' : '';?>>Переезд</option>
                    <option value="4" <?=($arResult['PATIENT_CARD']['ARCHIVE'] == 4) ? 'selected' : '';?>>Причина не известна</option>
                </select>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                <lable>Члены</lable>
            </td>
            <td>
                <textarea name="FAMILY_ID" id="" cols="30" rows="10"><?=$arResult['PATIENT_CARD']['FAMILY_ID']?></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <lable>Общий счет</lable>
            </td>
            <td>
                <input type="checkbox" name="JOINT_ACCOUNT" id="" <?=($arResult['PATIENT_CARD']['JOINT_ACCOUNT'] == 1) ? 'checked' : '';?>>
            </td>
        </tr>
    </table>
    <input type="submit" value="Сохранить">
</form>