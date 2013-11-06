// MindFight - онлайн-игра, мозговой штурм по типу БрейнРинга. Командная и одиночная игра. Еще в разработке.

<script>
    $("#page").addClass("padded");
    function game_time_left(){
        var endtime = parseInt($('#game_endtime').text()) - 1;
        $("#game_endtime").text(endtime);
        var hour = (Math.floor(endtime / 3600));
        var min = (Math.floor(endtime / 60) - (hour * 60));
        var sec = endtime % 60;

        if(hour>0) {
            var text = hour + " ч " + min + " мин";
        }

        if(hour==0 && min>0) {
            var text = min + " мин " + sec + " сек";
        }
        if(hour==0 && min==0) {
            if(sec>0) {
                var text = sec + " сек";
            }
        }
        $("#game_endtime_minsec").text(text);
    }
    
    function gonext(data) {
        var level = $("#level_id").text();
        if(data.gametype==1) {
            $("#level_content").html("<img style='margin:100px 350px' src='/media/img/bigloader.gif'>");
            $.get("/game/getlevel", { id:level,game:data.id_game }, function(dat) {
                $("#level_content").html(dat);
                $("#stat_auto").html("<img src='/media/img/ajax-loader.gif'/>");
            });
            }
        }
            // штурм - убираем поля для ввода, меняем цвет уровня в списке
        else {
            $("#level_options").remove();
            $("#level_content").append("<div class='level_completed'>Уровень пройден!</div>");
            $("#level_"+level).addClass('level_done');
        }
    }


    function answer(){
        var answer = $("#main_answer").val();
        var level = $("#level_id").text();
        $("#answer_status").html("<img src='/media/img/ajax-loader.gif'/>");
        $.post("/game/answer", { level:level, type:1, answer:answer }, function(data){
            if(data.status=='wrong') {
                $("#answer_status").addClass("wrong_answer");
                $("#answer_status").html("Ответ <b>"+answer+"</b> не верный.");
            }
            else {
                $("#answer_status").removeClass("wrong_answer");
                $("#answer_status").addClass("right_answer");
                $("#answer_status").html("Ответ <b>"+$("#main_answer").val()+"</b> верный.");
                $.each(data.sectors, function(i, item) {
                    $("#sector_"+item).html("<span class='right_answer'>"+$("#main_answer").val()+"</span>");
                });
                if(data.done==1) {
                    gonext(data);
                }
                if(data.done==2) {
                    window.location.href = "/game/finish/"+data.id_game;
                }
            }
        },"json");
    }

    function answerbonus(id){
        var answer = $("#bonus_answer_"+id).val();
        $("#bonus_status_"+id).html("<img src='/media/img/ajax-loader.gif'/>");
        $.post("/game/answer", { level:id, type:2, answer:answer }, function(data){
            if(data.status=='wrong'){
                $("#bonus_status_"+id).html("Ответ <b>"+answer+"</b> не верный.");
                $("#bonus_status_"+id).addClass("wrong_answer");
                $("#bonus_answer_"+id).val('');
            }
            else {
                $("#bonus_status_"+id).html("Ответ <b>"+answer+"</b> верный! Вы получили <b>"+data.time+"</b> бонусного времени." + "<p class='bonus_info'>"+data.text+"</p>");
                $("#bonus_status_"+id).removeClass("wrong_answer");
                $("#bonus_status_"+id).addClass("right_answer");
                $("#bonus_table_"+id).remove();
            }
        },"json");
    }

    function accepthint(id) {
        if (confirm("Подтвердите получение подсказки со штрафом!")) {
            $.get("/game/accepthint", { id: id }, function(data) {
                $('#hint_content').html(data);
            });
        }
    }

    function processHints() {
        $(".timeleft").each(function() {
            var timeleft = parseInt($(this).text()) - 1;
            $(this).text(timeleft);
            var id = $(this).attr('id').slice(9);
            var hour = (Math.floor(timeleft / 3600));
            var min = (Math.floor(timeleft / 60) - (hour * 60));
            var sec = timeleft % 60;

            if(hour>0) {
                var text = hour + " ч " + min + " мин " + sec + " сек";
                $("#hint_timer_"+id).text(text);
            }

            if(hour==0 && min>0) {
                var text = min + " мин " + sec + " сек";
                $("#hint_timer_"+id).text(text);
            }

            if(hour==0 && min==0) {
                if(sec>0) {
                    var text = sec + " сек";
                    $("#hint_timer_"+id).text(text);
                }
            }

            if(hour==0 && min==0 && sec==0){
                $("#hint_"+id).html("<img src='/media/img/ajax-loader.gif'/>");
                $.get("/game/gethint", {id:id}, function(data) {
                    $("#hint_"+id).html(data);
                });
            }

        });
    }

    function autogo(){
        if($('#level_auto')) {
            var timer = parseInt($("#level_auto").text());
            if(timer>0) {
                $("#level_auto").text(timer - 1);

                var hour = (Math.floor(timer / 3600));
                var min = (Math.floor(timer / 60) - (hour * 60));
                var sec = timer % 60;

                if(hour>0) {
                    var text = hour + " ч " + min + " мин ";
                }

                if(hour==0 && min>0) {
                    var text = min + " мин " + sec + " сек";
                }

                if(hour==0 && min==0) {
                    if(sec>0) {
                        var text = sec + " сек";
                    }
                }

                $("#stat_auto").text(text);

                if(sec==1) {
                    $.post("/game/autocomplete", { id:$("#level_id")}, function(data) {
                        if(data.done==1) {
                            gonext(data);
                        }
                        if(data.done==2) {
                            window.location.href = "/game/finish/"+data.id_game;
                        }
                    });
                }

            }
            else {
                $("#stat_auto").text("Нет");
            }
        }
    }

    setInterval(processHints, 1000);
    setInterval(game_time_left,1000);
    setInterval(autogo,1000);
</script>
<?
  $this->setPageTitle("Игра #".$model->id." : ".$model->title. "- MindFight.ru");
  $this->renderPartial("/game/_stat",array("model"=>$level));
?>
<a name='gamestart'></a>

<h1 class='shadow'><span class='marque'>Игра #<?= $model->id ?>:</span> <?= $model->title ?></h1>

<div class='game_play'>
  <?
  if($model->mode=='1') {
      $this->renderPartial("/level/_nav",array("model"=>$model));
      $this->renderPartial("/level/view",array("model"=>$level));
  } else {
      $this->renderPartial("/level/parallel",array("models"=>$model->levels,'level'=>$level));
  }
  
  ?>
</div>
