<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use MageRocket\GoCuotas\Helper\Data;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;

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
     * @var Data $helper
     */
    protected Data $helper;

    /**
     * @var CacheTypeList $cacheTypeList
     */
    protected cacheTypeList $cacheTypeList;

    /**
     * @param Data $helper
     * @param Random $random
     * @param WriterInterface $writer
     * @param CacheTypeList $cacheTypeList
     * @param EncryptorInterface $encryptor
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        Data $helper,
        Random $random,
        WriterInterface $writer,
        CacheTypeList $cacheTypeList,
        EncryptorInterface $encryptor,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->helper = $helper;
        $this->random = $random;
        $this->writer = $writer;
        $this->encryptor = $encryptor;
        $this->cacheTypeList = $cacheTypeList;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            $secret = self::GOCUTAS_SECRET_PREFIX . $this->random->getRandomString(25);
            $this->writer->save(
                self::GOCUOTAS_SECRET_XML_PATH,
                $this->encryptor->encrypt($secret)
            );
            $this->cacheTypeList->cleanType('config');
        } catch (LocalizedException $e) {
            $this->helper->log($e->getMessage());
        }
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
