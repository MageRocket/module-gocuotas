<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class GenerateSecretPatch implements DataPatchInterface
{
    // Secret XML Path
    protected const GOCUOTAS_SECRET_XML_PATH = 'payment/gocuotas/secret';

    // Secret Prefix. DO NOT CHANGE!
    private const GOCUTAS_SECRET_PREFIX = 'ag';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var WriterInterface $writer
     */
    private WriterInterface $writer;

    /**
     * @var Random $random
     */
    private Random $random;

    /**
     * @var EncryptorInterface $encryptor
     */
    protected EncryptorInterface $encryptor;

    /**
     * @param Random $random
     * @param WriterInterface $writer
     * @param EncryptorInterface $encryptor
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        Random $random,
        WriterInterface $writer,
        EncryptorInterface $encryptor,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->random = $random;
        $this->writer = $writer;
        $this->encryptor = $encryptor;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade.
     *
     * @return void
     * @throws LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        // Write Secret
        $secret = $this->random->getRandomString(10);
        $this->writer->save(
            self::GOCUOTAS_SECRET_XML_PATH,
            $this->encryptor->encrypt(self::GOCUTAS_SECRET_PREFIX . $secret)
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
