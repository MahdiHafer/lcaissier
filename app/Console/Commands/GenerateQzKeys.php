<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateQzKeys extends Command
{
    protected $signature = 'qz:generate-keys {--days=3650 : Validity in days}';
    protected $description = 'Generate QZ Tray signing certificate and private key (PEM)';

    public function handle()
    {
        if (!function_exists('openssl_pkey_new')) {
            $this->error('OpenSSL PHP extension is required.');
            return 1;
        }
        $opensslConfigPath = $this->detectOpenSslConfigPath();

        $days = max((int) $this->option('days'), 30);
        $targetDir = storage_path('app/qz');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $privateKeyPath = $targetDir . DIRECTORY_SEPARATOR . 'private-key.pem';
        $certificatePath = $targetDir . DIRECTORY_SEPARATOR . 'certificate.pem';

        $dn = [
            'countryName' => 'MA',
            'stateOrProvinceName' => 'Marrakech-Safi',
            'localityName' => 'Marrakech',
            'organizationName' => 'LCAISSIER',
            'organizationalUnitName' => 'POS',
            'commonName' => 'LCAISSIER QZ Signing',
            'emailAddress' => 'no-reply@lcaissier.local',
        ];

        $opensslArgs = [
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ];
        if ($opensslConfigPath) {
            $opensslArgs['config'] = $opensslConfigPath;
        }

        $privateKey = openssl_pkey_new($opensslArgs);

        if (!$privateKey) {
            $this->error('Failed to generate private key.');
            $this->printOpenSslErrors();
            return 1;
        }

        $csrArgs = ['digest_alg' => 'sha256'];
        if ($opensslConfigPath) {
            $csrArgs['config'] = $opensslConfigPath;
        }

        $csr = openssl_csr_new($dn, $privateKey, $csrArgs);
        if (!$csr) {
            $this->error('Failed to generate CSR.');
            $this->printOpenSslErrors();
            return 1;
        }

        $signArgs = ['digest_alg' => 'sha256'];
        if ($opensslConfigPath) {
            $signArgs['config'] = $opensslConfigPath;
        }

        $certificate = openssl_csr_sign($csr, null, $privateKey, $days, $signArgs);
        if (!$certificate) {
            $this->error('Failed to sign certificate.');
            $this->printOpenSslErrors();
            return 1;
        }

        $privateKeyPem = '';
        $certificatePem = '';

        $exportKeyArgs = [];
        if ($opensslConfigPath) {
            $exportKeyArgs['config'] = $opensslConfigPath;
        }

        $exportedKey = openssl_pkey_export($privateKey, $privateKeyPem, null, $exportKeyArgs);
        $exportedCert = openssl_x509_export($certificate, $certificatePem);

        if (!$exportedKey || trim($privateKeyPem) === '') {
            $this->error('Failed to export private key PEM.');
            $this->printOpenSslErrors();
            return 1;
        }

        if (!$exportedCert || trim($certificatePem) === '') {
            $this->error('Failed to export certificate PEM.');
            $this->printOpenSslErrors();
            return 1;
        }

        $writtenKey = file_put_contents($privateKeyPath, $privateKeyPem);
        $writtenCert = file_put_contents($certificatePath, $certificatePem);

        if ($writtenKey === false || $writtenKey <= 0) {
            $this->error('Failed to write private key file.');
            return 1;
        }

        if ($writtenCert === false || $writtenCert <= 0) {
            $this->error('Failed to write certificate file.');
            return 1;
        }

        $this->info('QZ keys generated successfully:');
        $this->line(' - ' . $certificatePath);
        $this->line(' - ' . $privateKeyPath);
        $this->newLine();
        $this->line('Set in .env:');
        $this->line('QZ_CERT_PATH=storage/app/qz/certificate.pem');
        $this->line('QZ_PRIVATE_KEY_PATH=storage/app/qz/private-key.pem');
        $this->line('QZ_PRIVATE_KEY_PASSPHRASE=');

        return 0;
    }

    private function detectOpenSslConfigPath(): ?string
    {
        $configured = getenv('OPENSSL_CONF');
        if ($configured && is_file($configured)) {
            return $configured;
        }

        $candidates = [
            PHP_BINARY ? dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf' : null,
            PHP_BINARY ? dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'openssl.cnf' : null,
            base_path('openssl.cnf'),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function printOpenSslErrors(): void
    {
        while ($error = openssl_error_string()) {
            $this->line(' - ' . $error);
        }
    }
}
