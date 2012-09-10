<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
    public function actionLogin()
    {
        $model=new LoginForm;

        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login())
            {
                //ucenter
                Yii::import('application.vendors.*');
                include_once 'ucenter.php';
                $script = uc_user_synlogin(Yii::app()->user->id);

                $this->render('/user/loginsuc', array(
                    'script' => $script,
                ));
                Yii::app()->end();
            }
        }
        // display the login form
        $this->render('/user/login',array('model'=>$model));
    }

    /**
	 * Logs out the current user and redirect to homepage.
	 */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        //ucenter
        Yii::import('application.vendors.*');
        include_once 'ucenter.php';
        $script = uc_user_synlogout();
        $this->render('/user/logoutsuc', array(
            'script' => $script,
        ));
        Yii::app()->end();
    }


    public function actionRegister() {
        $model = new RegisterForm;

        // ajax validator
        if(isset($_POST['ajax']) && $_POST['ajax']==='registration-form')
        {
            echo UActiveForm::validate(array($model));
            Yii::app()->end();
        }

        if (Yii::app()->user->id>1) {
            $this->redirect(Yii::app()->controller->module->profileUrl);
        } else {
            if(isset($_POST['RegisterForm'])) {
                $model->attributes=$_POST['RegisterForm'];

                if($model->validate())
                {
                    //ucenter
                    Yii::import('application.vendors.*');
                    include_once 'ucenter.php';
                    $uid = uc_user_register($model->username, $model->password, $model->email);
                    if($uid>0)
                    {
                        $soucePassword = $model->password;
                        $model->id = $uid;
                        $model->password=md5($model->password);
                        $model->last_login_time=time();
                        $model->last_login_ip=Yii::app()->getRequest()->userHostAddress;
                        $model->superuser=0;

                        $user=new User();
                        foreach($user->attributes as $uk=>$uv){
                            $user->$uk=$model->$uk;
                        }
                        if ($user->save()) {
                            Yii::app()->user->setFlash('registration',"Thank you for your registration.");
                            $identity=new UserIdentity($model->username,$soucePassword);
                            $identity->authenticate();
                            Yii::app()->user->login($identity,0);
                        }
                    }

                }
            }
            $this->render('/user/register',array('model'=>$model));
        }
    }

    public function actionRecovery() {
        $form = new UserRecoveryForm;
        if (Yii::app()->user->id) {
            $this->redirect(Yii::app()->controller->module->returnUrl);
        } else {
            $email = ((isset($_GET['email']))?$_GET['email']:'');
            $activkey = ((isset($_GET['activkey']))?$_GET['activkey']:'');
            if ($email&&$activkey) {
                $form2 = new UserChangePassword;
                $find = User::model()->findByAttributes(array('email'=>$email));
                if(isset($find)&&$find->activkey==$activkey) {
                    if(isset($_POST['UserChangePassword'])) {
                        $form2->attributes=$_POST['UserChangePassword'];
                        if($form2->validate()) {
                            $find->password = User::encrypting($form2->password);
                            $find->activkey = User::encrypting(microtime().$form2->password);
                            $find->save();
                            Yii::import('application.vendors.*');
                            include_once 'ucenter.php';
                            $status=uc_user_edit($find->username , '' , $form2->password , '' , true);
                            if($status==1 || $status==0){
                                Yii::app()->user->setFlash('recoveryMessage',"新的密码已经保存.");
                                $this->redirect(Yii::app()->user->recoveryUrl);
                            }
                        }
                    }
                    $this->render('/user/changepassword',array('form'=>$form2));
                } else {
                    Yii::app()->user->setFlash('recoveryMessage',"错误的恢复链接.");
                    $this->redirect(Yii::app()->user->recoveryUrl);
                }
            } else {
                if(isset($_POST['UserRecoveryForm'])) {
                    $form->attributes=$_POST['UserRecoveryForm'];
                    if($form->validate()) {
                        $user = User::model()->findbyPk($form->user_id);
                        if(empty($user)){
                            $user=new User;
                            $user->id=$form->user_id;
                            $user->username=$form->username;
                            $user->email=$form->email;
                            $user->password=User::encrypting(microtime());
                            $user->activkey=User::encrypting(microtime());
                        }

                        $user->save();

                        $activation_url = 'http://' . $_SERVER['HTTP_HOST'].$this->createUrl('site/recovery',array("activkey" => $user->activkey, "email" => $user->email));

                        $subject = Yii::t("你请求恢复 {site_name} 网站的用户密码",
                            array(
                                '{site_name}'=>Yii::app()->name,
                            ));
                        $message = Yii::t("你请求恢复 {site_name} 网站的用户密码. 设置新的密码请前往 {activation_url} ",
                            array(
                                '{site_name}'=>Yii::app()->name,
                                '{activation_url}'=>$activation_url,
                            ));

                        Yii::sendMail($user->email,$subject,$message);

                        Yii::app()->user->setFlash('recoveryMessage',"请检查你的邮箱. 密码恢复指导已经发到你的邮箱地址.");
                        $this->refresh();
                    }
                }
                $this->render('/user/recovery',array('form'=>$form));
            }
        }
    }
}