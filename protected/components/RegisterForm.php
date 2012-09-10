<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class RegisterForm extends CFormModel
{
    public $id;
    public $username;
    public $password;
    public $verifyPassword;
    public $email;
    public $verifyCode;
    public $last_login_time;
    public $last_login_ip;
    public $superuser=0;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
            // username and password are required
            array('username, password, verifyPassword, email, verifyCode', 'required'),
            array('username', 'length', 'max'=>20, 'min'=>5),
            // 用户名唯一性验证
//            array('username', 'unique','caseSensitive'=>false,''=>'user','message'=>'用户名"{value}"已经被注册，请更换'),
            array('username', 'checkname'),
            // 密码一致性验证
            array('verifyPassword', 'compare', 'compareAttribute'=>'password','message'=>'两处输入的密码并不一致'),
            // 电子邮件验证
            array('email', 'email'),
            // 电子邮件唯一性
            //array('email', 'unique','caseSensitive'=>false,'className'=>'user','message'=>'电子邮件"{value}"已经被注册，请更换'),
            array('email', 'checkemail'),
            //array('birthday', 'match', 'pattern'=>'%^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$%', 'allowEmpty'=>true, 'message'=>'生日必须是年-月-日格式'),
            //array('mobile', 'length', 'max'=>11, 'min'=>11, 'tooLong'=>'手机号码错误','tooShort'=>'手机号码错误'),
            array('verifyCode', 'captcha', 'allowEmpty'=> false),
        );
    }

    public function checkname($attribute,$params)
    {
        //ucenter
        Yii::import('application.vendors.*');
        include_once 'ucenter.php';
        $flag = uc_user_checkname($this->username);

        switch($flag)
        {
            case -1:
                $this->addError('username', '用户名不合法');
                break;
            case -2:
                $this->addError('username','包含不允许注册的词语');
                break;
            case -3:
                $this->addError('username','用户名已经存在');
                break;
        }
    }

    public function checkemail($attribute,$params)
    {
        //ucenter
        Yii::import('application.vendors.*');
        include_once 'ucenter.php';
        $flag = uc_user_checkemail($this->email);

        switch($flag)
        {
            case -4:
                $this->addError('email', 'Email 格式有误');
                break;
            case -5:
                $this->addError('email','Email 不允许注册');
                break;
            case -6:
                $this->addError('email','该 Email 已经被注册');
                break;
        }
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'username'=>'设定用户名',
            'password'=>'设定密码',
            'verifyPassword'=>'再次输入密码',
            'email'=>'电子邮件地址',
            'mobile'=>'手机号码',
            'verifyCode'=>'验证码',
        );
    }

    /**
     * 注册用户
     * @return boolean whether register is successful
     */
    public function register($uid)
    {
        //ucenter
        Yii::import('application.vendors.*');
        include_once 'ucenter.php';
        $uid = uc_user_register($this->username, $this->password, $this->email);
        if($uid>0)
        {
            $model = new user;
            $model->attributes = $_POST['RegisterForm'];
            $model->password = User::encrypting($_POST['RegisterForm']['password']);
            $model->id = $uid;

            return $model->save();
        }
    }

}