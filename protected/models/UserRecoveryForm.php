<?php

/**
 * UserRecoveryForm class.
 * UserRecoveryForm is the data structure for keeping
 * user recovery form data. It is used by the 'recovery' action of 'UserController'.
 */
class UserRecoveryForm extends CFormModel {
	public $username, $email, $user_id;
	
	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('username, email', 'required'),
			// password needs to be authenticated
			array('username', 'checkexists'),
		);
	}
	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'username'=>"用户名",
            'email'=>"邮件地址",
		);
	}
	
	public function checkexists($attribute,$params) {
		if(!$this->hasErrors())  // we only want to authenticate when no input errors
		{

            //ucenter
            Yii::import('application.vendors.*');
            include_once 'ucenter.php';
            list($this->user_id, $this->username, $email) = uc_get_user($this->username);

            if($this->user_id==0){
                $this->addError("username","用户不存在.");
            }elseif($this->user_id>0){
                if($email!=$this->email){
                    $this->addError("email","对应邮箱输入错误.");
                }
            }else{
                $this->addError("username","用户验证失败.");
            }
		}
	}
	
}