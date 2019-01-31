<?
/**
 * @var array $data
 */

use \Mmit\NewSmile\Rest\Documentation;
?>

<div class="container">
    <h1><?=$data['FULL_CODE']?></h1>

    <div class="row">
        <div class="col-md-12">
            <p><?=$data['DESCRIPTION']?></p>
            <p>
                <?if($data['PARAMS']):?>
                    Параметры команды:
                <?else:?>
                    Параметров нет.
                <?endif;?>
            </p>

            <?if($data['PARAMS']):?>
                <table class="table">
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
                            <td><?=$param->getTypeName()?></td>
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
            <?endif;?>

            <?if($data['RESULT_FORMAT']):?>
            <p>
                Формат результата:<br>

                <?
                /**
                 * @var \Mmit\NewSmile\Command\ResultFormat $resultFormat
                 */
                $resultFormat = $data['RESULT_FORMAT'];
                $fields = $resultFormat->getFields();
                $fieldsCount = count($fields);
                ?>
                <code class="result">
                    {<br>
                    <?foreach ($fields as $index => $field):?>
                        <?=Documentation\TemplateHelper::getResultFieldHtml($field);?>
                    <?endforeach;?>
                    }
                </code>

            </p>
            <?endif;?>
        </div>
    </div>

    <div class="row navigation">
        <div class="col-md-2 navigation-item">
            <a href="<?=$data['MAIN_PAGE_URL']?>" class="navigation-link">
                Главная страница
            </a>
        </div>
        <div class="col-md-2 navigation-item">
            <a href="<?=$data['ENTITY']['URL']?>" class="navigation-link">
                Сущность <?=$data['ENTITY']['CODE']?>
            </a>
        </div>
    </div>

</div>
