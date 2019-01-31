<div class="container">
    <h1><?=$data['CODE']?></h1>
    <div class="row">
        <p><?=$data['DESCRIPTION']?></p>
    </div>

    <?if($data['COMMANDS']):?>
        <table class="table">
            <thead>
            <tr>
                <th>Код</th>
                <th>Описание</th>
            </tr>
            </thead>

            <tbody>
            <?foreach ($data['COMMANDS'] as $command):?>
                <?
                /**
                 * @var \Mmit\NewSmile\Command\Base $command
                 */
                ?>
                <tr>
                    <td><a href="<?=$command['URL']?>"><?=$command['CODE']?></a></td>
                    <td><?=$command['DESCRIPTION']?></td>
                </tr>
            <?endforeach;?>
            </tbody>
        </table>
    <?endif;?>
</div>