<?
// MindFight - представление уровня
?>
<div id='level_content' class='level_content'>
<span style='display:none;' id='level_id'><?= $model->id ?></span>
<span style='display:none;' id='level_auto'><?= $model->getAuto(); ?></span>
<h2><span class='marque'>Уровень #<?= $model->number ?></span>  <?= $model->levelTitle(); ?></h2>

<div class='info-block'>
    <?= $model->text ?>
</div>

<? if(!$model->isCompleted()): ?>
    <div id='level_options'>
        <div class='answer'>
            <table cellspacing='0'>
                <tr>
                    <td><input type='text' class='main_answer' id='main_answer'></td>
                    <td><a href='' class='button blue2 widebutton' onclick='answer(); return false;'>Ответ</a></td>
                </tr>
            </table>
        </div>

        <div class='sectors'>
            <? if(sizeof($model->sectors)>1): ?>
                <p style='margin-bottom:2px;' class='marque'>Для прохождения уровня требуется несколько ответов:</p>
                <table cellspacing='0'>
                    <col width='274px'>
                    <col width='78px'>
                    <? foreach($model->sectors as $sector): ?>
                        <tr>
                            <td><?= $sector->label ?>:</td>
                            <td style='text-align: center;' id='sector_<?= $sector->id ?>'>нет ответа</td>
                        </tr>
                    <? endforeach; ?>
                </table>
            <? endif; ?>
        </div>

        <? if(sizeof($model->hints)): ?>
            <div class='game_hints'>
                <? foreach($model->hints as $k=>$hint): ?>
                    <?= $this->renderPartial("/hint/view",array("model"=>$hint,"k"=>$k+1)); ?>
                <? endforeach; ?>
            </div>
        <? endif; ?>
    </div>
<? else: ?>
    <div class='level_completed'>Уровень пройден!</div>
<? endif; ?>

<div id='answer_status'></div>


<? if(sizeof($model->bonuses)): ?>
    <div class='game_bonuses'>
        <? foreach($model->bonuses as $k=>$bonuslevel): ?>
            <?= $this->renderPartial("/bonus/view",array("model"=>$bonuslevel->bonus,"k"=>$k+1)); ?>
        <? endforeach; ?>
    </div>
<? endif; ?>

</div>
