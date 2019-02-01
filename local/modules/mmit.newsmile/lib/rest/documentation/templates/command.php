<?
/**
 * @var array $data
 */

use \Mmit\NewSmile\Rest\Documentation;
use \Mmit\NewSmile\CommandVariable;
?>

<div class="container">
    <a class="back-arrow" href="<?=$data['ENTITY']['URL']?>"></a>

    <h1><?=$data['FULL_CODE']?></h1>

    <div class="row">
        <div class="col-md-12">
            <p><?=$data['DESCRIPTION']?></p>
            <h2>Параметры команды:</h2>

            <?if($data['PARAMS']):?>
                <table class="table param-table">
                    <thead>
                    <tr>
                        <th>Код</th>
                        <th>Описание</th>
                        <th>Тип</th>
                        <th>По умолчанию</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?foreach ($data['PARAMS'] as $param):?>
                        <tr>
                            <?
                            /**
                             * @var \Mmit\NewSmile\CommandVariable\Base $param
                             */

                            ?>
                            <td><?=$param->getCode() . ($param->isRequired() ? '*' : '')?></td>
                            <td><?=$param->getDescription()?></td>
                            <td>
                                <?
                                if($param instanceof CommandVariable\ArrayParam)
                                {
                                    if($param->getContentType() instanceof CommandVariable\Object)
                                    {
                                        echo Documentation\TemplateHelper::getArrayFieldValueHtml($param, false);
                                    }
                                    else
                                    {
                                        echo $param->getTypeName();
                                    }
                                }
                                else
                                {
                                    echo $param->getTypeName();
                                }
                                ?>
                            </td>
                            <td>
                                <?
                                if(!$param->isRequired() && $param->getDefaultValue() !== null)
                                {
                                    $param->printValue($param->getDefaultValue());
                                }
                                ?>
                            </td>
                        </tr>
                    <?endforeach;?>
                    </tbody>
                </table>
            <?else:?>
                <p>Параметров нет.</p>
            <?endif;?>

            <?if($data['RESULT_FORMAT']):?>

                <h2>Формат результата:</h2>

                <?
                /**
                 * @var \Mmit\NewSmile\Command\ResultFormat $resultFormat
                 */
                $resultFormat = $data['RESULT_FORMAT'];

                if($resultFormat->getFields()):

                    $fields = $resultFormat->getFields();
                          $fieldsCount = count($fields);
                          ?>
                    <div class="result-object">
                        <?foreach ($fields as $index => $field):?>
                            <?=Documentation\TemplateHelper::getObjectFieldHtml($field);?>
                        <?endforeach;?>
                    </div>
                <?else:?>
                    Пустой объект
                <?endif;?>
            <?endif;?>
        </div>
    </div>

    <!--<div class="row navigation">
        <div class="col-md-2 navigation-item">
            <a href="<?/*=$data['MAIN_PAGE_URL']*/?>" class="navigation-link">
                Главная страница
            </a>
        </div>
        <div class="col-md-2 navigation-item">
            <a href="<?/*=$data['ENTITY']['URL']*/?>" class="navigation-link">
                Сущность <?/*=$data['ENTITY']['CODE']*/?>
            </a>
        </div>
    </div>-->

</div>
