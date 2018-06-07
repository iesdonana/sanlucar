<?php

namespace app\models;

/**
 * This is the model class for table "trayectos".
 *
 * @property int $id
 * @property string $origen
 * @property string $destino
 * @property int $conductor_id
 * @property string $fecha
 * @property string $plazas
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UsuariosId $conductor
 * @property Preferencias $preferencias
 */
class Trayectos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trayectos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['origen', 'destino', 'conductor_id', 'fecha', 'plazas'], 'required'],
            [['conductor_id'], 'default', 'value' => null],
            [['conductor_id'], 'integer'],
            [['fecha', 'created_at', 'updated_at'], 'safe'],
            [['plazas'], 'number'],
            [['origen', 'destino'], 'string', 'max' => 255],
            [['conductor_id'], 'exist', 'skipOnError' => true, 'targetClass' => UsuariosId::className(), 'targetAttribute' => ['conductor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'origen' => 'Origen',
            'destino' => 'Destino',
            'conductor_id' => 'Conductor Id',
            'fecha' => 'Fecha',
            'plazas' => 'Plazas',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Comprueba si el trayecto está completo.
     * @return bool True si el trayecto está completo, false si no lo está.
     */
    public function estaCompleto()
    {
        if ($this->plazas == 0) {
            return true;
        }
        return false;
    }

    /**
     * Comprueba si el trayecto está completo.
     * @return bool True si el trayecto está completo, false si no lo está.
     */
    public function haFinalizado()
    {
        $now = date('Y-m-d H:i');
        if ($now >= $this->fecha) {
            return true;
        }
        return false;
    }

    /**
     * Devuelve solo la calle de la dirección de origen.
     * @return string La calle de origen.
     */
    public function getOrigen()
    {
        return explode(',', $this->origen)[0];
    }

    /**
     * Devuelve solo la calle de la dirección de destino.
     * @return string La calle de destino.
     */
    public function getDestino()
    {
        return explode(',', $this->destino)[0];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConductor()
    {
        return $this->hasOne(UsuariosId::className(), ['id' => 'conductor_id'])->inverseOf('trayectos');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreferencias()
    {
        return $this->hasOne(Preferencias::className(), ['trayecto_id' => 'id'])->inverseOf('trayecto');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPasajeros()
    {
        return $this->hasMany(Pasajeros::className(), ['trayecto_id' => 'id'])->inverseOf('trayecto');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSolicitudes()
    {
        return $this->hasMany(Solicitudes::className(), ['trayecto_id' => 'id'])->inverseOf('trayecto');
    }
}
