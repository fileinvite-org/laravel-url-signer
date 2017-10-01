<?php

namespace Lab66\UrlSigner\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

use Lab66\UrlSigner\Exceptions\GenerateException;
use Lab66\UrlSigner\Exceptions\MissingConfiguration;

class UrlSignerGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url-signer:generate {complexity?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new public/private key used to sign urls.';

    /**
     * The complexity level of the RSA key.
     *
     * @var int
     */
    private $complexity;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ( ! $this->doesConfigurationFileExist() )
        {
            throw new MissingConfiguration('The configuration file does not exist please run: php artisan vendor:publish --tag=url-signer');
        }

        $this->complexity = (int) $this->argument('complexity') ?: 512;

        list($privateKey, $publicKey) = $this->generateRandomKeyPair();

        $this->setKeyInConfigFile('private_key', $privateKey);
        $this->setKeyInConfigFile('public_key', $publicKey);

        dd(compact('privateKey', 'publicKey'));
    }
    
    /**
     * Get the configuration file path
     * 
     * @return string
     */
    public function getConfigurationFilePath()
    {
        return $this->laravel->configPath() . '/url-signer.php';
    }

    /**
     * Does the configuration file exist.
     *
     * @return bool
     */
    public function doesConfigurationFileExist()
    {
        return file_exists($this->getConfigurationFilePath());
    }

    /**
     * Generate a random key pair.
     * 
     * @return string
     */
    public function generateRandomKeyPair()
    {
        $keys = openssl_pkey_new([
            'digest_alg'       => 'sha256',
            'private_key_bits' => $this->complexity,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);

        if ($keys === false)
        {
            throw new GenerateException(openssl_error_string());
        }
        
        openssl_pkey_export($keys, $private_key);
        
        $public_key = openssl_pkey_get_details($keys)['key'];
        
        return [$private_key, $public_key];
    }

    /**
     * The regex pattern to replace the config variable
     * 
     * @param  string $property The configuration property
     * @return string
     */
    public function keyReplacementPattern($property)
    {
        return '/(["\']'. $property . '["\']\s+=>\s+["\']).+(["\'])/isU';
    }

    /**
     * Update the configuration file with the new key at property.
     * 
     * @param string $property The configuration property name
     * @param string $key      The new key value
     */
    public function setKeyInConfigFile($property, $key)
    {
        file_put_contents($this->getConfigurationFilePath(), preg_replace(
            $this->keyReplacementPattern($property),
            '$1' . trim($key) . '$2',
            file_get_contents($this->getConfigurationFilePath())
        ));
    }

}
