// MindFight - переключение между уровнями

<script>
    function changelevel(level,game){
       $(".level_nav > ul > li").removeClass('level_active');
       $("#level_"+level).addClass('level_active');
       $("#level_content").html("<img style='margin:100px 350px' src='/media/img/bigloader.gif'>");
       $.get("/game/getlevel", { id:level,game:game }, function(data) {
            $("#level_content").html(data);
            $("#stat_auto").html("<img src='/media/img/ajax-loader.gif'/>");
       });
    }
</script>

<div class='level_nav'>
    <h2>Все уровни (<?= sizeof($models); ?>)</h2>
    <ul>
    <? foreach($models as $k=>$model): ?>
        <? $class = ""; ?>
        <? if($model->number == $level->number) $class = "level_active"; ?>
        <? if($model->isCompleted()) $class = "level_done"; ?>
        <li id='level_<?= $model->number ?>' class='grey2 <?= $class ?>' onclick="changelevel(<?= $model->number ?>, <?= $model->id_game ?>); return false;"'> #<?= $model->number ?><?= $model->levelTitle(); ?></li>
    <? endforeach; ?>
    </ul>
</div>

<? $this->renderPartial("/level/view",array("model"=>$level)); ?>

<div class='clear'></div>
