<?php $this->pageTitle=Yii::app()->name . ' - 修改密码';
?>

<div class="tonglan memberrg black" xmlns="http://www.w3.org/1999/html">
<div class="form">
<?php echo CHtml::beginForm(); ?>
    <div class="findpwd">
            <?php echo "Minimal password length 4 symbols."; ?></p>
        <?php echo CHtml::errorSummary($form); ?>

        <div class="row">
        <?php echo CHtml::activeLabelEx($form,'password'); ?>
        <?php echo CHtml::activePasswordField($form,'password', array('class'=>'text235')); ?>
            <p class="hint"></p>
        </div>

        <div class="row">
        <?php echo CHtml::activeLabelEx($form,'verifyPassword'); ?>
        <?php echo CHtml::activePasswordField($form,'verifyPassword', array('class'=>'text235')); ?>
            <p class="hint"></p>
        </div>
    </div>
	
	
	<div class="paddingbotton80 paddingtop50" id="but">
	<?php echo CHtml::submitButton("Save"); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
    <div class="clear"></div>
</div>