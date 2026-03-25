<?php

namespace Ometra\Apollo\Proteus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

/**
 * Modelo para gestionar aplicaciones Proteus.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $hash
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class ProteusApp extends Model
{
    use SoftDeletes;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'proteus_apps';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'hash',
    ];

    /**
     * Los atributos que deben ocultarse en la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'hash',
    ];

    /**
     * Obtiene el tenant asociado a la aplicación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(config('proteus.tenant_model', 'App\Models\Tenant'));
    }

    /**
     * Cifra un token usando cifrado reversible AES (Illuminate\Support\Facades\Crypt).
     *
     * @param string $token
     * @return string
     */
    public static function encryptToken(string $token): string
    {
        return Crypt::encryptString($token);
    }

    /**
     * Descifra un token cifrado.
     *
     * @param string $encryptedToken
     * @return string
     */
    public static function decryptToken(string $encryptedToken): string
    {
        return Crypt::decryptString($encryptedToken);
    }

    /**
     * Verifica si un token descifrado coincide con el almacenado.
     *
     * @param string $token Token descifrado
     * @param string $encryptedHash Hash cifrado almacenado
     * @return bool
     */
    public static function verifyToken(string $token, string $encryptedHash): bool
    {
        try {
            return $token === self::decryptToken($encryptedHash);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Crea una nueva aplicación Proteus.
     * 
     * Espera un token descifrado que será cifrado automáticamente.
     *
     * @param array $data Debe contener 'token' como clave
     * @return self
     */
    public static function createApp(array $data): self
    {
        if (isset($data['token'])) {
            $data['hash'] = self::encryptToken($data['token']);
            unset($data['token']);
        }
        return self::create($data);
    }

    /**
     * Obtiene una aplicación por su hash.
     *
     * @param string $hash
     * @return self|null
     */
    public static function findByHash(string $hash): ?self
    {
        return self::where('hash', $hash)->first();
    }

    /**
     * Obtiene todas las aplicaciones de un tenant.
     *
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByTenant(int $tenantId)
    {
        return self::where('tenant_id', $tenantId)->get();
    }

    /**
     * Actualiza una aplicación.
     *
     * @param array $data
     * @return bool
     */
    public function updateApp(array $data): bool
    {
        return $this->update($data);
    }

    /**
     * Elimina una aplicación (soft delete).
     *
     * @return bool|null
     */
    public function deleteApp(): ?bool
    {
        return $this->delete();
    }

    /**
     * Restaura una aplicación eliminada.
     *
     * @return bool
     */
    public function restoreApp(): bool
    {
        return $this->restore();
    }

    /**
     * Regenera el hash de la aplicación con un nuevo token.
     *
     * @param string $newToken El nuevo token descifrado
     * @return bool
     */
    public function regenerateHash(string $newToken): bool
    {
        return $this->update([
            'hash' => self::encryptToken($newToken),
        ]);
    }

    /**
     * Verifica si un token descifrado es válido para esta aplicación.
     *
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool
    {
        return self::verifyToken($token, $this->hash);
    }
}
