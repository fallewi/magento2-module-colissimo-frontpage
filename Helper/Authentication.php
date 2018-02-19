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
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

/**
 * Authentication helper.
 *
 * @author Smile (http://www.smile.fr)
 */
class Authentication extends AbstractHelper
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var CacheInterface */
    protected $cache;

    /**  @var ConfigHelper */
    protected $configHelper;

    /** @var Curl */
    protected $curl;

    /** @var string */
    protected $token;

    /**
     * @param Context $context
     * @param CacheInterface $cache
     * @param ConfigHelper $configHelper
     * @param Curl $curl
     */
    public function __construct(
        Context $context,
        CacheInterface $cache,
        ConfigHelper $configHelper,
        Curl $curl
    ) {
        parent::__construct($context);
        $this->logger = $context->getLogger();
        $this->cache = $cache;
        $this->configHelper = $configHelper;
        $this->curl = $curl;
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
        $data = [
            'login'     => $this->configHelper->getAuthenticationWebserviceLogin(),
            'password'  => $this->configHelper->getAuthenticationWebservicePassword(),
        ];

        $this->curl->setHeaders(
            [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Charset'       => 'utf-8',
                'Content-Length'=> strlen(http_build_query($data)),
            ]
        );

        // Send a request to the authentication webservice
        try {
            $this->curl->post($this->configHelper->getAuthenticationWebserviceUrl(), $data);
            $response = json_decode($this->curl->getBody());
            $token = isset($response->token) ? $response->token : false;
        } catch (\Exception $e) {
            $this->logger->alert($e->getMessage());
            $token = false;
        }

        return $token;
    }
}
