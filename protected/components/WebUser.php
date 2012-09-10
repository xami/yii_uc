<?php
class WebUser extends CWebUser
{
    public function afterLogin($fromCookie)
    {
        parent::afterLogin ( $fromCookie );

        //ucenter
        Yii::import ( 'application.vendors.*' );
        include_once 'ucenter.php';

        $script = uc_user_synlogin ( $this->getId () );
        $count = preg_match_all ( '/src="(.+?)"/i', $script, $matches );

        if ($count > 0) {
            foreach ( $matches [1] as $file ) {
                Yii::app ()->clientScript->registerScriptFile ( $file, CClientScript::POS_END );
            }
        }
        //局部刷新顶部登录状态
        Yii::app()->clientScript->registerScript('refresh-login-status', 'top.$("#top_nav").load("'.CHtml::normalizeUrl(array('/site/login_status')).'");');
    }

    public function afterLogout()
    {
        parent::afterLogout();
        //ucenter
        Yii::import ( 'application.vendors.*' );
        include_once 'ucenter.php';

        $script = uc_user_synlogout();
        $count = preg_match_all ( '/src="(.+?)"/i', $script, $matches );

        if ($count > 0) {
            foreach ( $matches [1] as $file ) {
                Yii::app ()->clientScript->registerScriptFile ( $file, CClientScript::POS_END );
            }
        }
        Yii::app()->clientScript->registerScript('refresh-login-status', 'top.$("#top_nav").load("'.CHtml::normalizeUrl(array('/site/login_status')).'");');
    }
}