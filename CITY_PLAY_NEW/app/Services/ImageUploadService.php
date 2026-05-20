<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class ImageUploadService
{
    /**
     * Valide et téléverse une image vers le disque public local.
     * 
     * @param UploadedFile $file
     * @param string $folder Dossier cible (ex: 'places', 'riddles')
     * @return string URL de l'image stockée
     * @throws Exception
     */
    public function upload(UploadedFile $file, string $folder = 'cityplay'): string
    {
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|image|mimes:jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            throw new Exception("Fichier invalide : JPEG/PNG de 2Mo max attendu.");
        }

        $path = Storage::disk('public')->putFile("images/{$folder}", $file, 'public');

        if (!$path) {
            throw new Exception('Impossible de téléverser l\'image.');
        }

        return Storage::url($path);
    }

    /**
     * Supprime une image stockée sur le disque public local.
     * 
     * @param string $url URL complète ou relative de l'image
     * @return bool
     */
    public function delete(string $url): bool
    {
        try {
            $path = parse_url($url, PHP_URL_PATH);
            if (!$path) {
                return false;
            }

            $relativePath = ltrim(preg_replace('#^/storage/#', '', $path), '/');
            return Storage::disk('public')->delete($relativePath);
        } catch (Exception $e) {
            return false;
        }
    }
}
