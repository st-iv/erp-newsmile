<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="FILE" id="">
    <button type="submit">Загрузить</button>
</form>
<table>
    <?foreach ($arResult['FILES'] as $arFile):?>
        <tr>
            <td>
                <?
                if (CFile::IsImage($arFile["FILE_NAME"], $arFile["CONTENT_TYPE"])) {
                    ?><img src="<?=CFile::GetPath($arFile["ID"])?>" alt=""><?
                } else {
                    ?><a href="<?=CFile::GetPath($arFile["ID"])?>" download=""></a><?
                }
                ?>
            </td>
        </tr>
    <?endforeach;?>
</table>