<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class ConfiguracionBanco extends Model
{
    use HasFactory;

    protected $table = 'configuracion_bancos';

    protected $fillable = [
        'user_name',
        'password',
        'numero_cuenta',
        'api_key',
        'nombre_empresa',
        'codigo_empresa',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'configuracion_banco_id');
    }
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    public function setNumeroCuentaAttribute($value)
    {
        $this->attributes['numero_cuenta'] = Crypt::encryptString($value);
    }

    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getNumeroCuentaAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function scopePorCodigoEmpresa($query, $codigoEmpresa)
    {
        return $query->where('codigo_empresa', $codigoEmpresa);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
