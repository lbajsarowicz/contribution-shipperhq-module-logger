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

use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel
{
    /**
     * @var \ShipperHQ\Logger\Model\ResourceModel\Log
     */
    private $logResource;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \ShipperHQ\Logger\Model\ResourceModel\Log $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \ShipperHQ\Logger\Model\ResourceModel\Log $resource,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->logResource = $resource;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('ShipperHQ\Logger\Model\ResourceModel\Log');
    }

    /**
     * Retrieve Latest Notice
     *
     * @return \ShipperHQ\Logger\Model\Log
     */
    public function loadLatestNotice()
    {
        $this->setData([]);
        $this->logResource->loadLatestNotice($this);
        return $this;
    }

    /**
     * Retrieve notice statuses
     *
     * @return array
     */
    public function getNoticeStatus()
    {
        return $this->logResource->getNoticeStatus($this);
    }

    /**
     * Parse and save new data
     *
     * @param array $data
     * @return \ShipperHQ\Logger\Model\Log
     */
    public function parse($severity, $extension, $title, $description, $code = 0, $url = '')
    {
        if (is_array($description) || is_object($description)) {
            $description = print_r($description, true);
        }

        $feedData[] = [
            'severity'      => $severity,
            'date_added'    => gmdate('Y-m-d H:i:s'),
            'extension'     => $extension,
            'title'         => $title,
            'description'   => $description,
            'code'          => $code,
            'url'           => $url
        ];

        return $this->logResource->parse($this, $feedData);
    }

    public function truncate()
    {
        return $this->logResource->truncate();
    }
}
