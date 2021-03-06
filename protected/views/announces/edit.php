<?php
/**
 * @var Book $book
 * @var Announce $post
 */

	$this->pageTitle = $book->fullTitle . " - " . ($post->isNewRecord ? "Нов анонс" : "Редакция на анонса");
?>
<style type='text/css'>
	#Announce_body {height:200px;}
</style>
<script type="text/javascript">
$(function() {
	$("#form-edit").submit(function(e) {
		if(!confirm("Вие прочетохте анонса си и сте сигурни, че сте готови да го публикувате на главната страница на сайта, където ще го прочетат много хора?")) {
			e.preventDefault();
			return false;
		}
		return true;
	});
	$("#form-edit button.remove").click(function(e) {
		if(confirm("Сигурен ли сте?")) $("#form-rm").submit();
	});
});
</script>

<h1><?=$post->isNewRecord ? "Нов анонс" : "Редакция на анонс"; ?> относно превода „<?=$book->fullTitle; ?>“</h1>

<form id="form-rm" method="post" action="<?=$post->getUrl("remove"); ?>"><input type="hidden" name="really" value="1"/></form>

<?php
	/** @var TbActiveForm $form */
	$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
		"id" => "form-edit",
		"type" => "horizontal",
		"htmlOptions" => array(
			"enctype" => "multipart/form-data",
		),
	));

	echo $form->errorSummary($post);

	$topics = Yii::app()->params["blog_topics"]["announce"];
	echo $form->radioButtonListInlineRow($post, "topics", $topics);

echo $form->textAreaRow($post, "body", array("class" => "span6", "hint" => "Тук може да използвате HTML-таговете a, b, strong, i, em, u, small, sub, sup."));
?>
<div class="form-actions">
<?php
	echo CHtml::htmlButton("<i class='icon-ok icon-white'></i> Запис", array("type" => "submit", "class" => "btn btn-primary")) . " ";
	if(!$post->isNewRecord && Yii::app()->user->can("blog_moderate")) echo CHtml::htmlButton("<i class='icon-ban-circle icon-white'></i> Изтриване", array("class" => "btn btn-danger remove")) . " ";
	if($post->isNewRecord) {
		$back = $post->book->getUrl("announces");
	} else {
		$back = $post->url;
	}
	echo CHtml::htmlButton("<i class='icon-remove icon-white'></i> Отмяна", array("onclick" => "location.href='{$back}'", "class" => "btn btn-success"));
?>
</div>
<?php $this->endWidget(); ?>
