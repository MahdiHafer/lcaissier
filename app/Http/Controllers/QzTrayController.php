<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QzTrayController extends Controller
{
    public function certificate()
    {
        try {
            $cert = $this->loadPem('QZ_CERT_PEM', 'QZ_CERT_PATH', 'certificat QZ');
            return response($cert, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
        } catch (\Throwable $e) {
            return response('QZ certificate error: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    public function sign(Request $request)
    {
        $dataToSign = (string) $request->input('request', '');
        if ($dataToSign === '') {
            return response()->json(['error' => 'Champ request requis'], 422);
        }

        if (!function_exists('openssl_sign')) {
            return response()->json(['error' => 'Extension OpenSSL indisponible'], 500);
        }

        try {
            $privateKeyPem = $this->loadPem('QZ_PRIVATE_KEY_PEM', 'QZ_PRIVATE_KEY_PATH', 'cle privee QZ');
            $passphrase = (string) env('QZ_PRIVATE_KEY_PASSPHRASE', '');
            $keyResource = openssl_pkey_get_private($privateKeyPem, $passphrase !== '' ? $passphrase : null);

            if (!$keyResource) {
                return response()->json(['error' => 'Cle privee QZ invalide'], 500);
            }

            $signature = '';
            $ok = openssl_sign($dataToSign, $signature, $keyResource, OPENSSL_ALGO_SHA512);
            openssl_free_key($keyResource);

            if (!$ok) {
                return response()->json(['error' => 'Echec generation signature'], 500);
            }

            return response()->json([
                'signature' => base64_encode($signature),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'QZ sign error: ' . $e->getMessage()], 500);
        }
    }

    private function loadPem(string $inlineEnvKey, string $pathEnvKey, string $label): string
    {
        $inline = (string) env($inlineEnvKey, '');
        if ($inline !== '') {
            $pem = str_replace("\\n", "\n", $inline);
            if (stripos($pem, 'BEGIN') !== false) {
                return $pem;
            }
        }

        $path = (string) env($pathEnvKey, '');
        if ($path !== '') {
            $resolvedPath = $this->resolvePath($path);
            if (!is_file($resolvedPath)) {
                throw new \RuntimeException("Fichier introuvable pour $label");
            }
            $pem = file_get_contents($resolvedPath);
            if (!$pem) {
                throw new \RuntimeException("Impossible de lire le fichier pour $label");
            }
            return $pem;
        }

        throw new \RuntimeException("Configuration manquante pour $label");
    }

    private function resolvePath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:\\\\|^\//', $path)) {
            return $path;
        }

        $baseCandidate = base_path($path);
        if (is_file($baseCandidate)) {
            return $baseCandidate;
        }

        $storageCandidate = storage_path($path);
        if (is_file($storageCandidate)) {
            return $storageCandidate;
        }

        return $path;
    }
}
