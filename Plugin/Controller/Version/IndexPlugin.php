<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Plugin\Controller\Version;

use Magento\Framework\App\Response\HttpFactory;
use Magento\Version\Controller\Index\Index;
use MageRocket\Core\Model\ModuleInfoProvider;

class IndexPlugin
{

    /**
     * @var HttpFactory $httpFactory
     */
    private HttpFactory $httpFactory;

    /**
     * @var ModuleInfoProvider $moduleInfoProvider
     */
    private ModuleInfoProvider $moduleInfoProvider;

    /**
     * @param HttpFactory $httpFactory
     * @param ModuleInfoProvider $moduleInfoProvider
     */
    public function __construct(
        HttpFactory $httpFactory,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->httpFactory = $httpFactory;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * After Execute
     *
     * @param Index $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(Index $subject, $result)
    {
        if ((method_exists($result, 'setContents')) && (method_exists($result, 'renderResult'))) {
            try {
                $moduleData = $this->moduleInfoProvider->getModuleInfo('MageRocket_GoCuotas');
                $dummyResponse = $this->httpFactory->create();
                $result->renderResult($dummyResponse);
                $content = $dummyResponse->getBody() . __(
                    ' %1 Go Cuotas v%2 (%3) by MageRocket',
                    [
                        stripos($dummyResponse, 'with') ? ' and ' : ' with ',
                        $moduleData['version'],
                        isset($moduleData['extra']['dev']) ? 'Development' : 'Production'
                    ]
                );
                $result->setContents($content);
            } catch (\Exception $exception) {
                ; // Do nothing, we don't want to break legacy Magento here.
            }
        }
        return $result;
    }
}
