<?php

/**
 * This is the model class for table "{{user}}".
 *
 * The followings are the available columns in table '{{user}}':
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $last_login_ip
 * @property string $last_login_time
 * @property string $activkey
 * @property integer $superuser
 * @property integer $status
 */
class User extends CActiveRecord
{
    public $captcha = array('register'=>true);
    public static function doCaptcha($place = '') {
        $user=new User();
        if(!extension_loaded('gd'))
            return false;
        if (in_array($place, $user->captcha))
            return $user->captcha[$place];
        return false;
    }

    public static function encrypting($string="") {
        $hash = 'md5';
        if ($hash=="md5")
            return md5($string);
        if ($hash=="sha1")
            return sha1($string);
        else
            return hash($hash,$string);
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{user}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username, password, email, activkey', 'required'),
			array('superuser, status', 'numerical', 'integerOnly'=>true),
			array('username, last_login_ip', 'length', 'max'=>20),
			array('password, email, activkey', 'length', 'max'=>128),
			array('last_login_time', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, username, password, email, last_login_ip, last_login_time, activkey, superuser, status', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => 'Username',
			'password' => 'Password',
			'email' => 'Email',
			'last_login_ip' => 'Last Login Ip',
			'last_login_time' => 'Last Login Time',
			'activkey' => 'Activkey',
			'superuser' => 'Superuser',
			'status' => 'Status',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('last_login_ip',$this->last_login_ip,true);
		$criteria->compare('last_login_time',$this->last_login_time,true);
		$criteria->compare('activkey',$this->activkey,true);
		$criteria->compare('superuser',$this->superuser);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}