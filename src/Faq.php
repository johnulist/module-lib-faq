<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\ModuleLibFaq;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Retrieve the FAQ of the module
 */
class Faq
{
    const BASE_URL = 'https://api.addons.prestashop.com/request/faq/';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * Method to call in case of error
     *
     * @var callable|null
     */
    private $errorCallable;

    /**
     * @param string $moduleKey
     * @param string $psVersion
     * @param string $isoCode
     */
    public function __construct($moduleKey, $psVersion, $isoCode, array $options = [])
    {
        $this->parameters = (new Parameters())
            ->setModuleKey($moduleKey)
            ->setPsVersion($psVersion)
            ->setIsoCode($isoCode);

        // Allow client options to be customized
        $options = array_merge_recursive([
            'base_url' => self::BASE_URL,
            'defaults' => [
                'timeout' => 10,
            ],
        ], $options);

        $this->client = new Client($options);
    }

    /**
     * Wrapper of method post from guzzle client
     *
     * @return array|false return response or false if no response
     */
    public function getFaq()
    {
        try {
            $response = $this->client->post($this->parameters->getFaqUri());
        } catch (RequestException $e) {
            if (is_callable($this->errorCallable)) {
                call_user_func($this->errorCallable, $e);
            }

            if (!$e->hasResponse()) {
                return false;
            }
            $response = $e->getResponse();
        }

        $data = json_decode($response->getBody(), true);

        return !empty($data['categories']) ? $data : false;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return self
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Allow modules to run specific code in case of exception
     *
     * @return self
     */
    public function setErrorCallable(callable $c)
    {
        $this->errorCallable = $c;

        return $this;
    }
}
