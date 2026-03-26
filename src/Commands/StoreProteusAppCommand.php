<?php

namespace Ometra\Apollo\Proteus\Commands;

use Illuminate\Console\Command;
use Ometra\Apollo\Proteus\Models\ProteusApp;

/**
 * Comando para crear una nueva aplicación Proteus.
 *
 * Uso:
 * php artisan proteus:app:store --tenant_id=1 --token="token_secreto"
 * php artisan proteus:app:store
 *
 * Nota: El nombre de la aplicación se obtiene de la variable de entorno PROTEUS_APP_NAME
 */
class StoreProteusAppCommand extends Command
{
    /**
     * Nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'proteus:app:store
                            {--tenant_id= : ID del tenant}
                            {--token= : Token descifrado de la aplicación}';

    /**
     * Descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Crea una nueva aplicación Proteus con token descifrado';

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            // Obtener tenant_id
            $tenantId = $this->option('tenant_id');
            if (!$tenantId) {
                $tenantId = $this->ask('¿Cuál es el ID del tenant?');
            }

            if (!is_numeric($tenantId) || $tenantId <= 0) {
                $this->error('El ID del tenant debe ser un número positivo.');
                return self::FAILURE;
            }

            // Obtener nombre de la aplicación desde variable de entorno
            // Primero intenta PROTEUS_APP_NAME, si no existe usa APP_NAME
            $name = ucfirst(strtolower(env('PROTEUS_APP_NAME') ?? env('APP_NAME')));
            
            if (empty(trim($name))) {
                $this->error('Las variables de entorno PROTEUS_APP_NAME o APP_NAME no están configuradas.');
                return self::FAILURE;
            }

            // Obtener token descifrado
            $token = $this->option('token');
            if (!$token) {
                $token = $this->ask('¿Cuál es el token descifrado de la aplicación?');
            }

            if (empty(trim($token))) {
                $this->error('El token descifrado no puede estar vacío.');
                return self::FAILURE;
            }

            // Verificar si la aplicación ya existe
            if (ProteusApp::where('name', $name)->exists()) {
                $this->error("Ya existe una aplicación con el nombre: {$name}");
                return self::FAILURE;
            }

            // Crear la aplicación con el token proporcionado
            $app = ProteusApp::createApp([
                'tenant_id' => (int) $tenantId,
                'name' => $name,
                'token' => $token,
            ]);

            $this->info('✓ Aplicación Proteus creada exitosamente.');
            $this->line('');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $app->id],
                    ['Tenant ID', $app->tenant_id],
                    ['Nombre', $app->name],
                    ['Hash Cifrado', substr($app->hash, 0, 32) . '...'],
                    ['Creada el', $app->created_at],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al crear la aplicación: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
