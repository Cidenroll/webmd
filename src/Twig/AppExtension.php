<?php


namespace App\Twig;


use App\Services\ProfileUploaderHelper;
use App\Services\UploaderHelper;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath']),
            new TwigFunction('profile_asset', [$this, 'getUploadedProfilePath']),
        ];
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->container->get(UploaderHelper::class)->getPublicPath($path);
    }

    public function getUploadedProfilePath(string $path): string
    {
        return $this->container->get(ProfileUploaderHelper::class)->getPublicPath($path);
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedServices()
    {
        return [
            UploaderHelper::class,
            ProfileUploaderHelper::class
        ];
    }
}