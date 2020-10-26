<?php

namespace LinnikSA\Composer\SmartSourceUrl;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\Vcs\GitLabDriver;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
    }

    /**
     * @inheritDoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @inheritDoc
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::PRE_PACKAGE_INSTALL => [['prePackageInstall', PHP_INT_MAX]],
        ];
    }

    public function prePackageInstall(PackageEvent $event)
    {
        $operation = $event->getOperation();

        if ($operation instanceof InstallOperation) {
            $this->handlePackageInstallation($operation->getPackage());
        }
    }

    protected function handlePackageInstallation(PackageInterface $package)
    {
        if (!$package instanceof Package) {
            return;
        }

        $url = $package->getSourceUrl();

        if (!preg_match(GitLabDriver::URL_REGEX, $url, $match)) {
            return;
        }

        // we handle only git urls
        $domain = $match['domain2'] ?? null;
        if (!$domain) {
            return;
        }

        $config = $this->composer->getConfig();
        $gitlabDomains = (array) $config->get('gitlab-domains');
        $gitlabOauth = (array) $config->get('gitlab-oauth');
        $gitlabTokens = (array) $config->get('gitlab-token');
        $httpBasicAuth = (array) $config->get('http-basic');

        if (
            !\in_array($domain, $gitlabDomains, true) &&
            !isset($httpBasicAuth[$domain])
        ) {
            return;
        }


        $hasHttpAuth =
            isset($gitlabOauth[$domain]) ||
            isset($gitlabTokens[$domain]) ||
            isset($httpBasicAuth[$domain])
        ;

        if ($hasHttpAuth) {
            $url = 'https://' . $domain . '/' . $match['parts'] . '/' . $match['repo'] . '.git';

            $sourceUrlRef = (new \ReflectionObject($package))->getProperty('sourceUrl');
            $sourceUrlRef->setAccessible(true);
            $sourceUrlRef->setValue($package, $url);
        }
    }
}
