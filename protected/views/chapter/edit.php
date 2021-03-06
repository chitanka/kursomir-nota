<?php
	/**
	* @var Chapter $chap
	* @var integer $overridedId - ID главы с особыми правами доступа в этом переводе; 0, если такой нет; -1, если это единственная глава в переводе
	* @var boolean $ajax - если редактор встраивается в другую страницу
	*/
	$this->pageTitle = "Свойства на главата „{$chap->title}“ от превода „{$chap->book->fullTitle}“";
?>
<style type='text/css'>
	#override-table { display:none; }
	#status-msg { display: none; color:#700; margin: 10px 0 0;}
</style>
<script type="text/javascript">
var E = {
	init: function() {
		E.override();
		$("#Chapter_status").change(E.status_change).change();
	},
	status_change: function() {
		if($(this).val() == 3) $("#status-msg").show();
		else $("#status-msg").hide();
		console.log($(this).val());
	},
	rm: function() {
		$("#btn-remove").attr("disabled", true).text("Това може да отнеме няколко минути...").attr("title", "Тъмко време да изпиете една боза.");
		if(!confirm("Уверени ли сте?")) {
			$("#btn-remove").attr("disabled", false).text("Изтриване");
			return;
		}

		$("#form-rm").submit();
	},
	override: function() {
		if($("#override-checkbox").is(":checked")) $("#override-table").show();
		else $("#override-table").hide();
	}
};
$(E.init);
</script>

<?php if(!$ajax): ?><h1><?=$post->isNewRecord ? "Нова глава" : "Свойства на главата"; ?></h1><?php endif ?>

<form id="form-rm" method="post" action="<?=$chap->getUrl("remove"); ?>"><input type="hidden" name="really" value="1"/></form>

<?php
	/** @var TbActiveForm $form */
	$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
		"id" => "form-edit",
		"type" => "horizontal",
		"htmlOptions" => array(
			"style" => "margin-bottom:0;",
		),
	));

	echo $form->errorSummary($chap);

	echo $form->textFieldRow($chap, "title", array("class" => "span5"));
?>
<div class="control-group">
	<label class="control-label">Статус</label>
	<div class="controls">
		<?php echo $form->dropDownList($chap, "status", Yii::app()->params["translation_statuses"]); ?>
		<p id="status-msg">Добавянето на нови версии на превода и оценяването ще бъдат отключени!</p>
	</div>
</div>
<?php if($overridedId == 0 || $overridedId == $chap->id): ?>
<div class="control-group">
	<div class="controls">
		<label class="checkbox">
			<input type="checkbox" name="Chapter[has_override]" value="1" id="override-checkbox" <?=$chap->hasOverride ? "checked" : "" ?> onclick="E.override()" />
			Специални права за достъп (възможно е само за една глава)
		</label>
		<table class="table table-striped table-condensed table-oneline" id="override-table">
		<thead><tr>
			<th>Действие</th>
			<th class='c'>Всички</th>
			<th class='c'>Група</th>
			<th class='c'>Модератори</th>
			<th class='c'>Никой</th>
			<th class='c'>Както при превода</th>
		</tr></thead>
		<tbody>
		<?php
			foreach(Yii::app()->params["ac_areas_chap"] as $role => $title) {
				echo "<tr>";
				echo "<th>{$title}</th>";
				foreach(array("a", "g", "m", "o", "") as $v) {
					echo "<td class='c'>";
					if($role != "ac_read" or $v != "a") {
						echo "<input type='radio' name='Chapter[{$role}]' value='{$v}'" . ($chap->$role == $v ? " checked" : "") . ($chap->book->opts_get(Book::OPTS_BAN_COPYRIGHT) && $v == "a" ? " disabled" : "") . " />";
					}
					echo "</td>";
				}
			}
		?>
		</tbody>
		</table>
	</div>
</div>
<?php
	else:
?>
<p class="help-block">Даването на специални права за достъп е възможно само за една глава.</p>
<?php
	endif;

	echo "<div class='form-actions' style='margin-bottom:0'>";
	echo CHtml::htmlButton(
		"<i class='icon-ok icon-white'></i> Запис",
		array("type" => "submit", "class" => "btn btn-primary click-wait")
	) . " ";
	if(!$chap->isNewRecord) echo CHtml::htmlButton(
		"<i class='icon-ban-circle icon-white'></i> Изтриване",
		array("onclick" => "E.rm()", "class" => "btn btn-danger", "id" => "btn-remove")
	) . " ";
	echo CHtml::htmlButton(
		"Отмена",
		array("onclick" => $ajax ? "CE.cancel()" : ("location.href='" . ($chap->book->url) . "'"), "class" => "btn")
	);
	echo "</div>";

	$this->endWidget();
?>
