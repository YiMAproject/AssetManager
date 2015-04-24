<?php
namespace AssetManager\Asset;

use Assetic\Asset\FileAsset as BaseFileAsset;
use Assetic\Filter\FilterInterface;
use Assetic\Util\VarUtils;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class FileAsset extends BaseFileAsset
    implements
    ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    public function load(FilterInterface $additionalFilter = null)
    {
        $source = VarUtils::resolve($this->source, $this->getVars(), $this->getValues());

        if (!is_file($source))
            throw new \RuntimeException(sprintf('The source file "%s" does not exist.', $source));

        if (pathinfo($source,PATHINFO_EXTENSION) == 'php') {
            ob_start();
            include $source;
            $content = ob_get_clean();
        } else {
            $content = file_get_contents($source);
        }

        $this->doLoad($content, $additionalFilter);
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->sm = $serviceManager;
    }
}
 