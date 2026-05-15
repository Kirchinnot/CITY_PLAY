<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Exception;

class ImageUploadService
{
    /**
     * Valide et téléverse une image vers Cloudinary.
     * 
     * @param UploadedFile $file
     * @param string $folder Dossier cible (ex: 'places', 'riddles')
     * @return string URL de l'image stockée
     * @throws Exception
     */
    public function upload(UploadedFile $file, string $folder = 'cityplay'): string
    {
        // Validation interne (en plus de la validation des contrôleurs pour sécurité)
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|image|mimes:jpeg,png|max:2048', // 2Mo max
        ]);

        if ($validator->fails()) {
            throw new Exception("Fichier invalide : JPEG/PNG de 2Mo max attendu.");
        }

        // Téléversement vers Cloudinary
        $response = Cloudinary::uploadApi()->upload($file->getRealPath(), [
            'folder' => 'cityplay/' . $folder,
            'quality' => 'auto',
            'fetch_format' => 'auto'
        ]);
        return $response['secure_url'];
    }

    /**
     * Supprime une image de Cloudinary via son URL.
     * 
     * @param string $url URL complète de l'image
     * @return bool
     */
    public function delete(string $url): bool
    {
        try {
            // Extraction du public_id depuis l'URL Cloudinary
            // Format typique: https://res.cloudinary.com/cloud_name/image/upload/v12345/folder/public_id.jpg
            $path = parse_url($url, PHP_URL_PATH);
            $segments = explode('/', $path);
            
            // Le public_id commence après '/upload/vXXXX/'
            // On cherche l'index de 'upload' et on prend tout ce qui suit le segment de version (vXXXX)
            $uploadIndex = array_search('upload', $segments);
            if ($uploadIndex === false) return false;

            $publicIdWithExtension = implode('/', array_slice($segments, $uploadIndex + 2));
            $publicId = pathinfo($publicIdWithExtension, PATHINFO_DIRNAME) . '/' . pathinfo($publicIdWithExtension, PATHINFO_FILENAME);
            
            // Si le dirname est '.', on ne garde que le filename
            if (strpos($publicId, './') === 0) {
                $publicId = substr($publicId, 2);
            }

            Cloudinary::uploadApi()->destroy($publicId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
