<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Logger
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Logger\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \ShipperHQ\Logger\Model\Logger;

class PostLogObserver implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \ShipperHQ\Logger\Model\Logger
     */
    private $logger;

    /**
     *
     * @param \ShipperHQ\Logger\Model\Logger $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \ShipperHQ\Logger\Model\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Send type of logging required
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->scopeConfig->getValue(
            'shqlogmenu/shqlogger/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return ;
        }

        $adminLevel = $this->scopeConfig->getValue(
            'shqlogmenu/shqlogger/admin_level',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $systemLogLevel = $this->scopeConfig->getValue(
            'shqlogmenu/shqlogger/system_level',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $emailLevel = $this->scopeConfig->getValue(
            'shqlogmenu/shqlogger/email_level',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $code           = $observer->getEvent()->getCode();
        $url            = $observer->getEvent()->getUrl();
        $severity       = $observer->getEvent()->getSeverity();
        $title          = $observer->getEvent()->getTitle();
        $extension      = $observer->getEvent()->getExtension();
        $description    = $observer->getEvent()->getDescription();

        if ($systemLogLevel>0 && $systemLogLevel>=$severity) {
            switch ($severity) {
                case Logger::SEVERITY_NOTICE:
                    $this->logger->debug(var_export($code.' '.$url.' - '.$extension.' - '.$title, true));
                    if ($description !== null && $description != '') {
                        $this->logger->debug(var_export($description, true));
                    }
                    break;
                case Logger::SEVERITY_MINOR:
                    $this->logger->info(var_export($code.' '.$url.' - '.$extension.' - '.$title, true));
                    if ($description !== null && $description != '') {
                        $this->logger->info(var_export($description, true));
                    }
                    break;
                case Logger::SEVERITY_MAJOR:
                    $this->logger->warning(var_export($code.' '.$url.' - '.$extension.' - '.$title, true));
                    if ($description !== null && $description != '') {
                        $this->logger->warning(var_export($description, true));
                    }
                    break;
                case Logger::SEVERITY_CRITICAL:
                    $this->logger->critical(var_export($code.' '.$url.' - '.$extension.' - '.$title, true));
                    if ($description !== null && $description != '') {
                        $this->logger->critical(var_export($description, true));
                    }
                    break;
            }
        }
    }
}
