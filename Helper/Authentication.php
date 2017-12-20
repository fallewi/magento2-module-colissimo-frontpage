<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ColissimoFrontPage\Helper;

use LaPoste\ColissimoFrontPage\Helper\Config as ConfigHelper;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;

/**
 * Authentication helper.
 *
 * @author Smile (http://www.smile.fr)
 */
class Authentication extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        CacheInterface $cache,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->cache = $cache;
        $this->configHelper = $configHelper;
    }

    /**
     * Get Authentication Token.
     *
     * @return string
     */
    public function getAuthenticationToken()
    {
        if ($this->token === null) {
            $cacheKey = 'colissimofrontpage_authentication_token';
            $token = $this->cache->load($cacheKey);

            if ($token !== false) {
                $this->token =  $token;
            } else {
                $this->token = $this->generateNewToken();
                $this->cache->save($this->token, $cacheKey, [], 14);
            }
        }

        return $this->token;
    }

    /**
     * Generate a new authentication token.
     *
     * @return string
     */
    protected function generateNewToken()
    {
        $data = http_build_query(
            [
                'login'     => $this->configHelper->getAuthenticationWebserviceLogin(),
                'password'  => $this->configHelper->getAuthenticationWebservicePassword(),
            ]
        );

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->configHelper->getAuthenticationWebserviceUrl());
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $curlHandle,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/x-www-form-urlencoded',
                'Charset: utf-8',
                'Content-Length: ' . strlen($data)
            ]
        );

        // Send a request to the authentication webservice
        try {
            $output = curl_exec($curlHandle);
            $response = json_decode($output);
            $token = isset($response->token) ? $response->token : false;
        } catch (\Exception $e) {
            $this->logger->alert($e->getMessage());
            $token = false;
        }
        curl_close($curlHandle);

        return $token;
    }
}
