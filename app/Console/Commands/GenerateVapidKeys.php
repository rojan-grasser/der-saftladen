<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid';

    protected $description = 'Generate VAPID keys for Web Push notifications';

    public function handle(): int
    {
        // Try PHP OpenSSL first
        try {
            $keys = $this->generateViaPhp();
            $this->printKeys($keys['publicKey'], $keys['privateKey']);
            return self::SUCCESS;
        } catch (\Throwable) {
            // Fall through to Node.js
        }

        // Fallback: Node.js (always available in this project)
        try {
            $keys = $this->generateViaNode();
            $this->printKeys($keys['publicKey'], $keys['privateKey']);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Key generation failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function generateViaPhp(): array
    {
        // On Windows, openssl_pkey_new needs OPENSSL_CONF to be set
        if (PHP_OS_FAMILY === 'Windows' && empty(getenv('OPENSSL_CONF'))) {
            foreach ([
                'C:/Program Files/OpenSSL-Win64/bin/openssl.cfg',
                'C:/Program Files/OpenSSL/bin/openssl.cfg',
                'C:/Windows/openssl.cnf',
            ] as $path) {
                if (file_exists($path)) {
                    putenv('OPENSSL_CONF=' . $path);
                    break;
                }
            }
        }

        $key = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        if ($key === false) {
            throw new \RuntimeException('openssl_pkey_new failed');
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false) {
            throw new \RuntimeException('openssl_pkey_get_details failed');
        }

        $privateKeyDer = $details['ec']['d'];
        $x = $details['ec']['x'];
        $y = $details['ec']['y'];

        // Uncompressed public key: 0x04 || x || y
        $publicKeyRaw = "\x04" . str_pad($x, 32, "\x00", STR_PAD_LEFT) . str_pad($y, 32, "\x00", STR_PAD_LEFT);

        return [
            'publicKey' => rtrim(strtr(base64_encode($publicKeyRaw), '+/', '-_'), '='),
            'privateKey' => rtrim(strtr(base64_encode(str_pad($privateKeyDer, 32, "\x00", STR_PAD_LEFT)), '+/', '-_'), '='),
        ];
    }

    private function generateViaNode(): array
    {
        $script = <<<'JS'
const crypto = require('crypto');
const { privateKey } = crypto.generateKeyPairSync('ec', { namedCurve: 'P-256' });
const pub = privateKey.export({ type: 'pkcs8', format: 'der' });
const priv = privateKey.export({ type: 'sec1', format: 'der' });

// Extract raw private key (last 32 bytes of SEC1 DER)
const rawPriv = priv.slice(priv.length - 32);

// Extract uncompressed public key from PKCS8
const pubKey = crypto.createPublicKey(privateKey);
const pubDer = pubKey.export({ type: 'spki', format: 'der' });
const rawPub = pubDer.slice(pubDer.length - 65); // 0x04 + 32 + 32

const toBase64Url = b => b.toString('base64').replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
console.log(JSON.stringify({ pub: toBase64Url(rawPub), priv: toBase64Url(rawPriv) }));
JS;

        $tmpFile = sys_get_temp_dir() . '/vapid_gen_' . uniqid() . '.js';
        file_put_contents($tmpFile, $script);

        $output = shell_exec('node ' . escapeshellarg($tmpFile) . ' 2>&1');
        unlink($tmpFile);

        $data = json_decode(trim($output ?? ''), true);

        if (! isset($data['pub'], $data['priv'])) {
            throw new \RuntimeException('Node.js output invalid: ' . $output);
        }

        return ['publicKey' => $data['pub'], 'privateKey' => $data['priv']];
    }

    private function printKeys(string $public, string $private): void
    {
        $this->info('VAPID keys generated. Add these to your .env file:');
        $this->line('');
        $this->line('VAPID_PUBLIC_KEY=' . $public);
        $this->line('VAPID_PRIVATE_KEY=' . $private);
    }
}
