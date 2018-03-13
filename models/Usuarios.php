<?php

namespace app\models;

use Spatie\Dropbox\Exceptions\BadRequest;
use Yii;
use yii\imagine\Image;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;

/**
 * This is the model class for table "usuarios".
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $nombre
 * @property string $apellido
 * @property string $biografia
 * @property string $url_avatar
 * @property string $auth_key
 * @property string $token_val
 * @property string $created_at
 * @property string $updated_at
 */
class Usuarios extends \yii\db\ActiveRecord implements IdentityInterface
{
    const ESCENARIO_CREATE = 'create';
    const ESCENARIO_UPDATE = 'update';

    public $passwordRepeat;

    /**
     * Contiene la foto de perfil del usuario.
     * @var UploadedFile
     */
    public $foto;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuarios';
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['passwordRepeat', 'foto']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'nombre', 'apellido'], 'required'],
            [['password', 'passwordRepeat'], 'required', 'on' => self::ESCENARIO_CREATE],
            [['created_at', 'updated_at', 'url_avatar'], 'safe'],
            [['email', 'password', 'nombre', 'apellido', 'biografia', 'auth_key', 'token_val'], 'string', 'max' => 255],
            [
                ['passwordRepeat'],
                'compare',
                'compareAttribute' => 'password',
                'skipOnEmpty' => false,
                'on' => [self::ESCENARIO_UPDATE, self::ESCENARIO_CREATE],
                'message' => 'Las contraseñas deben ser iguales',
            ],
            [['email'], 'unique'],
            [['token_val'], 'unique'],
            [['email'], 'email'],
            [['foto'], 'file', 'extensions' => 'jpg, png'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'passwordRepeat' => 'Confirma tu contraseña',
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'biografia' => 'Biografía',
            'auth_key' => 'Auth Key',
            'token_val' => 'Token Val',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function upload()
    {
        if ($this->foto === null) {
            return true;
        }
        $nombre = Yii::getAlias('@uploads/' . $this->id . '.jpg');
        $res = $this->foto->saveAs($nombre);
        if ($res) {
            Image::thumbnail($nombre, 160, null)->save($nombre);
        }
        return $res;
    }

    public function uploadDropbox()
    {
        $id = $this->id;
        $fichero = "$id.jpg";
        $client = new \Spatie\Dropbox\Client(getenv('DROPBOX_TOKEN'));
        try {
            $client->delete($fichero);
        } catch (BadRequest $e) {
            // No se hace nada
        }
        $client->upload(
            $fichero,
            file_get_contents(Yii::getAlias("@uploads/$fichero")),
            'overwrite'
        );
        $res = $client->createSharedLinkWithSettings($fichero, [
            'requested_visibility' => 'public',
        ]);
        return $res['url'];
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword(
            $password,
            $this->password
        );
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->auth_key = Yii::$app->security->generateRandomString();
                if ($this->scenario === self::ESCENARIO_CREATE) {
                    $this->token_val = Yii::$app->security->generateRandomString();
                    $this->password = Yii::$app->security
                        ->generatePasswordHash($this->password);
                }
            } else {
                if ($this->scenario === self::ESCENARIO_UPDATE) {
                    if ($this->password === '') {
                        $this->password = $this->getOldAttribute('password');
                    } else {
                        $this->password = Yii::$app
                            ->security->generatePasswordHash($this->password);
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarioId()
    {
        return $this->hasOne(UsuariosId::className(), ['id' => 'id'])->inverseOf('usuario');
    }
}
