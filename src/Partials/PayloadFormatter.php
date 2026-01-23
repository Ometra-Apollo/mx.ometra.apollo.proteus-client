<?php

namespace Ometra\Apollo\Proteus\Partials;

use Illuminate\Http\UploadedFile;
use Exception;

class PayloadFormatter
{
    /**
     * Transforma un array asociativo en una estructura multipart para Guzzle.
     *
     * @param array $data
     * @return array
     */
    public function prepareMultipart(array $data): array
    {
        $multipart = [];

        foreach ($data as $key => $value) {
            if ($key === "transformations" && is_array($value)) {
                foreach ($value as $tKey => $t) {
                    $multipart[] = $this->formatTransformation($tKey, $t);
                }
                continue;
            }

            if (is_array($value)) {
                if ($this->isArrayOfFiles($value)) {
                    foreach ($value as $file) {
                        $multipart[] = $this->formatFile($key, $file);
                    }
                } else {
                    $multipart = array_merge($multipart, $this->handleDataArray($key, $value));
                }
                continue;
            }

            if ($value instanceof UploadedFile) {
                $multipart[] = $this->formatFile($key, $value);
                continue;
            }
            $multipart[] = $this->formatField($key, $value);
        }

        return $multipart;
    }

    /**
     * Maneja la conversión de un array de datos en partes multipart.
     * @param string $parentKey
     * @param array $values
     */
    private function handleDataArray(string $parentKey, array $values): array
    {
        $parts = [];
        foreach ($values as $subKey => $value) {
            $parts[] = [
                'name'     => "{$parentKey}[{$subKey}]",
                'contents' => is_array($value) ? json_encode($value) : $value,
            ];
        }
        return $parts;
    }

    /**
     * Formatea un archivo para multipart.
     *
     * @param string       $key
     * @param UploadedFile $file
     * @return array
     * @throws Exception Si el archivo no existe.
     */
    public function formatFile(string $key, UploadedFile $file): array
    {
        if (!file_exists($file->getPathname())) {
            throw new Exception("El archivo no existe en la ruta: " . $file->getPathname());
        }
        $name = str_ends_with($key, '[]') ? $key : $key . '[]';

        return [
            'name'     => $name,
            'contents' => fopen($file->getRealPath(), 'r'),
            'filename' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Determina si un array es un conjunto de archivos UploadedFile.
     * @param array $array
     * @return bool
     */
    private function isArrayOfFiles(array $array): bool
    {
        $first = reset($array);
        return $first instanceof UploadedFile;
    }

    /**
     * Formatea una transformación para multipart.
     * @param string $key
     * @param mixed  $value
     * @return array
     */
    public function formatTransformation(string $key, mixed $value): array
    {
        return [
            'name'     => "transformations[$key]",
            'contents' => $value['key'] ?? $value,
        ];
    }

    public function formatMetadata(string $key, mixed $value): array
    {
        return [
            'name'     => "metadata[$key]",
            'contents' => is_array($value) ? json_encode($value) : $value,
        ];
    }

    public function formatField(string $key, mixed $value): array
    {
        return [
            'name'     => $key,
            'contents' => is_array($value) ? json_encode($value) : $value,
        ];
    }
}
