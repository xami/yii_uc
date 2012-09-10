<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    public $id;
    /**
     * Authenticates a user.
     * The example implementation makes sure if the username and password
     * are both 'demo'.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        //ucenter
        Yii::import('application.vendors.*');
        include_once 'ucenter.php';
        list($uid, $username, $password, $email) = uc_user_login($this->username, $this->password);
        if($uid > 0)
        {
            $user = user::model()->findByPk($uid);

            if($user == null)//说明网站数据库中没有，而ucenter中有这个用户，添加用户
            {
                $user = new user;
                $user->username = $username;
                $user->password = User::encrypting($password);
                $user->email = $email;
                $user->id = $uid;
                $user->activkey=User::encrypting(microtime().$model->password);
                $user->save();

                $user->refresh();
            }

            $this->username = $user->username;
            $user->password = User::encrypting($password);
            $this->id = $user->id;

//            $user->last_login_time = $user->this_login_time;
            $user->last_login_time = time();
//            $user->last_login_ip = $user->this_login_ip;
            $user->last_login_ip = Yii::app()->getRequest()->userHostAddress;
            $user->save();


            $this->errorCode=self::ERROR_NONE;
        }
        elseif($uid == -1)
        {
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        }
        elseif($uid == -2)
        {
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        }

        return !$this->errorCode;
    }

    public function getId()
    {
        return $this->id;
    }
}