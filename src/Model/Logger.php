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

namespace ShipperHQ\Logger\Model;

use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * ShipperHQ Logger implementation
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Logger extends \Magento\Framework\Logger\Monolog
{
    public const SEVERITY_CRITICAL = 1;
    public const SEVERITY_MAJOR    = 2;
    public const SEVERITY_MINOR    = 3;
    public const SEVERITY_NOTICE   = 4;
    public const SEVERITY_NONE     = -1;

    /**
     * @var  \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @param LogFactory $logFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $name The logging channel
     * @param \Monolog\Handler\HandlerInterface[] $handlers Optional stack of handlers
     * @param callable[] $processors Optional array of processors
     */
    public function __construct(
        LogFactory $logFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $name,
        array $handlers = [],
        array $processors = []
    ) {

        $this->logFactory = $logFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        if (!$this->scopeConfig->getValue('shqlogmenu/shqlogger/active')) {
            return;
        }

        $this->logMessage($message, $context, self::SEVERITY_NOTICE);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        if (!$this->scopeConfig->getValue('shqlogmenu/shqlogger/active')) {
            return;
        }

        $this->logMessage($message, $context, self::SEVERITY_MINOR);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        if (!$this->scopeConfig->getValue('shqlogmenu/shqlogger/active')) {
            return;
        }

        $this->logMessage($message, $context, self::SEVERITY_MAJOR);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        if (!$this->scopeConfig->getValue('shqlogmenu/shqlogger/active')) {
            return;
        }
        $this->logMessage($message, $context, self::SEVERITY_CRITICAL);
    }

    /**
     * Dispatch a message to the configured logging destinations.
     *
     * @param mixed $message
     * @param array $context
     * @param int $severity
     * @return void
     */
    protected function logMessage($message, array $context, $severity)
    {
        $adminLevel = $this->scopeConfig->getValue('shqlogmenu/shqlogger/admin_level');
        $systemLogLevel = $this->scopeConfig->getValue('shqlogmenu/shqlogger/system_level');
        $emailLevel = $this->scopeConfig->getValue('shqlogmenu/shqlogger/email_level');

        if ($adminLevel>0 && $adminLevel >= $severity) {
            $this->logAdmin($message, $context, $severity);
        }

        if ($systemLogLevel > 0 && $systemLogLevel >= $severity) {
            $message = is_string($message) ? $message : var_export($message, true);
            switch ($severity) {
                case self::SEVERITY_NOTICE:
                    parent::debug($message, $context);
                    break;
                case self::SEVERITY_MINOR:
                    parent::notice($message, $context);
                    break;
                case self::SEVERITY_MAJOR:
                    parent::warning($message, $context);
                    break;
                case self::SEVERITY_CRITICAL:
                    parent::critical($message, $context);
                    break;
            }
        }

        if ($emailLevel>0 && $emailLevel >= $severity) {
            $this->logEmail($message, $context, $severity);
        }
    }

    /**
     * Store a structured message in the admin log.
     *
     * @param mixed $message
     * @param array $context
     * @param int $severity
     * @return void
     */
    protected function logAdmin($message, array $context, $severity)
    {
        if (!is_array($message)) {
            $message = explode('--', (string) $message);
        }
        if (is_array($message) && count($message) > 2) {
            $newLog = $this->logFactory->create();
            $newLog->parse($severity, $message[0], $message[1], $message[2]);
        }
    }

    /**
     * Reserve the email logging destination.
     *
     * @param mixed $message
     * @param array $context
     * @param int $severity
     * @return void
     */
    protected function logEmail($message, array $context, $severity)
    {
        //To Do
    }

    /**
     * Check the legacy per-module debug setting.
     *
     * @deprecated 20.4.0 Logging is now enabled or disabled globally.
     * @see debug()
     * @param string $moduleName
     *
     * @return bool
     */
    protected function isDebug($moduleName)
    {
        $path = 'shqlogmenu/shq_module_log/'.$moduleName;
        parent::debug('the path to config setting ', [$path]);

        return $this->scopeConfig->getValue($path) ? true : false;
    }
}
