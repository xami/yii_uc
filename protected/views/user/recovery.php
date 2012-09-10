<?php
$this->pageTitle=Yii::app()->name . ' - '.'恢复密码';
?>

<div class="tonglan memberrg black" xmlns="http://www.w3.org/1999/html">
<?php if(Yii::app()->user->hasFlash('recoveryMessage')): ?>
<div class="success">
<?php echo Yii::app()->user->getFlash('recoveryMessage'); ?>
</div>
<?php else: ?>

<div class="form">
<?php echo CHtml::beginForm(); ?>

	<?php echo CHtml::errorSummary($form); ?>
	
	<div class="findpwd">
        <p class="hint"><?php echo "请输入用户名和对应的邮箱地址"; ?></p>
		<p>
            <span><?php echo CHtml::activeLabel($form,'username'); ?></span>
		    <?php echo CHtml::activeTextField($form,'username', array('class'=>'text235')) ?>
            <span><?php echo CHtml::activeLabel($form,'email'); ?></span>
            <?php echo CHtml::activeTextField($form,'email', array('class'=>'text235')) ?>
        </p>

	</div>

    <div>
		<?php echo CHtml::submitButton("恢复"); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
<?php endif; ?>

    <div class="clear"></div>
</div>