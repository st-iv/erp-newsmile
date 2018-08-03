<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<form action="" method="post">
    <table>
        <tr>
            <td>Название</td>
            <td><input type="text" name="NAME" value="<?=$arResult['DOCTORS']['NAME']?>"></td>
        </tr>
        <tr>
            <td>Клиника</td>
            <td><input type="text" name="CLINIC_ID" value="<?=$arResult['DOCTORS']['CLINIC_ID']?>"></td>
        </tr>
    </table>
    <input type="submit" value="Сохранить">
</form>