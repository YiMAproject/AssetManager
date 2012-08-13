<?php

namespace AssetManager\Service;


use Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AssetManagerServiceFactory implements FactoryInterface
{
    /**
     * Creates the AssetManager
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AssetManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('config');
        $assetManager   = new AssetManager($config['asset_manager']);

        return $assetManager;
    }
}