<?php
namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\Exception;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Xing OAuth2 provider adapter.
 */
class Xing extends OAuth2
{

  /**
     * {@inheritdoc}
     */
    protected $scope = 'user-read-email';

    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = '';

    /**
     * {@inheritdoc}
     */
    public $authorizeUrl = '';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = '';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = '';


    




}
?>