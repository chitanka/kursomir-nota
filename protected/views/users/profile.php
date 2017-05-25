<?php
/**
* @var User $user
* @var User[] $invited
*/

Yii::app()->clientScript
	->registerScriptFile("/js/profile.js")->registerCssFile("/css/profile.css?3")
	->registerScript("profile", "Profile.uid = {$user->id};", CClientScript::POS_HEAD);

$this->pageTitle = $user->login . ": профил";

$this->renderPartial("profile_head", array("user" => $user));
?>

<table class="personal">
<?php
	foreach($user->userinfo as $ui) {
		if($ui->value == "" || $ui->valueFormatted == "" || ($ui->type == "int" and $ui->value == 0)) continue;
		echo "<tr>";
		echo "<th>{$ui->label}</th>";
		echo "<td>{$ui->valueFormatted}</td>";
		echo "</tr>";
	}
?>
</table>

<?php if($user->id == Yii::app()->user->id) { ?>
	<div><i class="icon-pencil"></i> <a href='<?=$user->getUrl("edit"); ?>' class='act'>Промяна на данните</a></div>
<?php } ?>

<?php if(count($invited) > 0): ?>
	<h2>Регистрирани преводачи по <?=$user->sexy("негова", "нейна", "негова"); ?> покана:</h2>
	<table class="items table table-condensed">
		<thead>
		<tr>
			<th>Псевдоним</th>
			<th>Карма</th>
			<th>Преводи</th>
			<th>Общ рейтинг</th>
			<th>Среден рейтинг</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($invited as $user) {
			echo "<tr>";
			echo "<td>" . $user->ahref . "</td>";
			printf("<td>%d</td>", $user->rate_u);
			printf("<td>%d</td>", $user->n_trs);
			printf("<td>%d</td>", $user->rate_t);
			echo "<td>" . ($user->n_trs ? sprintf("%.02f", $user->rate_t / $user->n_trs) : "") . "</td>";
		}
		?>
		</tbody>
	</table>
<?php endif; ?>
