<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Login',
);
?>

<h1>Login</h1>

<p>Please fill out the following form with your login credentials:</p>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<div class="row">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username'); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password'); ?>
		<?php echo $form->error($model,'password'); ?>
		<p class="hint">
			Hint: You may login with <kbd>demo</kbd>/<kbd>demo</kbd> or <kbd>admin</kbd>/<kbd>admin</kbd>.
		</p>
	</div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
	</div>

    <div id="nav" class="row">
        <p class="hint">
            <?php echo CHtml::link("注册",Yii::app()->user->registrationUrl); ?>
            |
            <?php echo CHtml::link("找回密码",Yii::app()->user->recoveryUrl); ?>
        <div id="append_parent"><div id="ls_fastloginfield_ctrl_menu" class="sltm" style="display: none; width: 40px;"><ul><li class="current">用户名</li><li>Email</li></ul></div></div>
        </p>
    </div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>



    <p id="backtoblog">
        <a title="不知道自己在哪？" href="/">← 返回首页</a>
    </p>

<?php $this->endWidget(); ?>
</div><!-- form -->
