<?
\Bitrix\Main\Loader::includeModule('mmit.newsmile');

function pr($var, $bDump = false)
{
    ?>
    <pre style="max-height: 250px;max-width: 600px;border: 1px solid red;font-size: 12px;overflow: scroll;"><?
        if($bDump)
        {
            var_dump($var);
        }
        else
        {
            print_r($var);
        }
        ?>
    </pre>
    <?
}