<?php

namespace AssetManager;

use AssetManager\Service\AssetManager;
use yimaBase\Mvc\MvcEvent;
use Zend\Loader\StandardAutoloader;
use Zend\Loader\AutoloaderFactory;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Console\Adapter\AdapterInterface;

/**
 * Module class
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    BootstrapListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Callback method for dispatch and dispatch.error events.
     *
     * @param MvcEvent $event
     */
    public function onEventError(MvcEvent $event)
    {
        /* @var $response \Zend\Http\Response */
        $response = $event->getResponse();
        if (!method_exists($response, 'getStatusCode') || $response->getStatusCode() !== 404)
            // Unknown Response
            return;

        $request        = $event->getRequest();
        $serviceManager = $event->getApplication()->getServiceManager();
        /** @var AssetManager $assetManager */
        $assetManager   = $serviceManager->get(__NAMESPACE__ . '\Service\AssetManager');
        if (!$assetManager->resolvesToAsset($request))
            // This is not a registered asset
            return;

        $response->setStatusCode(200);
        $assetManager->setAssetOnResponse($response);

        $event->setResult($response);
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        // Attach for dispatch, and dispatch.error (with low priority to make sure statusCode gets set)
        /* @var $eventManager \Zend\EventManager\EventManagerInterface */
        $eventManager = $event->getTarget()->getEventManager();
        $callback     = array($this, 'onEventError');
        $priority     = -1100;
        $eventManager->attach(MvcEvent::EVENT_ERROR, $callback, $priority);
    }

    /**
     * @param \Zend\Console\Adapter\AdapterInterface $console
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'Warmup',
            'assetmanager warmup [--purge] [--verbose|-v]' => 'Warm AssetManager up',
            array('--purge', '(optional) forces cache flushing'),
            array('--verbose | -v', '(optional) verbose mode'),
        );
    }
}
