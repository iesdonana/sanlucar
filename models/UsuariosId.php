<?php

namespace app\models;

/**
 * This is the model class for table "usuarios_id".
 *
 * @property int $id
 *
 * @property Coches[] $coches
 * @property Trayectos[] $trayectos
 * @property Usuarios $usuarios
 */
class UsuariosId extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuarios_id';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'id'])->inverseOf('usuarioId');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrayectos()
    {
        return $this->hasMany(Trayectos::className(), ['conductor_id' => 'id'])->inverseOf('conductor');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoches()
    {
        return $this->hasMany(Coches::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }
}
