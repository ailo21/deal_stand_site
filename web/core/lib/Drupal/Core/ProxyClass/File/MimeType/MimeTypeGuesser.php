<?php
// @codingStandardsIgnoreFile

/**
 * This file was generated via php core/scripts/generate-proxy-class.php 'Drupal\Core\File\MimeType\MimeTypeGuesser' "core/lib/Drupal/Core".
 */

namespace Drupal\Core\ProxyClass\File\MimeType {

  use Drupal\Core\DependencyInjection\DependencySerializationTrait;
  use Symfony\Component\DependencyInjection\ContainerInterface;
  use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

  /**
     * Provides a proxy class for \Drupal\Core\File\MimeType\MimeTypeGuesser.
     *
     * @see \Drupal\Component\ProxyBuilder
     */
    class MimeTypeGuesser implements MimeTypeGuesserInterface, \Symfony\Component\Mime\MimeTypeGuesserInterface
    {

        use DependencySerializationTrait;

        /**
         * The id of the original proxied service.
         *
         * @var string
         */
        protected $drupalProxyOriginalServiceId;

        /**
         * The real proxied service, after it was lazy loaded.
         *
         * @var \Drupal\Core\File\MimeType\MimeTypeGuesser
         */
        protected $service;

        /**
         * The service container.
         *
         * @var \Symfony\Component\DependencyInjection\ContainerInterface
         */
        protected $container;

        /**
         * Constructs a ProxyClass Drupal proxy object.
         *
         * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
         *   The container.
         * @param string $drupal_proxy_original_service_id
         *   The service ID of the original service.
         */
        public function __construct(ContainerInterface $container, $drupal_proxy_original_service_id)
        {
            $this->container = $container;
            $this->drupalProxyOriginalServiceId = $drupal_proxy_original_service_id;
        }

        /**
         * Lazy loads the real service from the container.
         *
         * @return object
         *   Returns the constructed real service.
         */
        protected function lazyLoadItself()
        {
            if (!isset($this->service)) {
                $this->service = $this->container->get($this->drupalProxyOriginalServiceId);
            }

            return $this->service;
        }

        /**
         * {@inheritdoc}
         */
        public function guessMimeType(string $path): string
        {
            return $this->lazyLoadItself()->guessMimeType($path);
        }

        /**
         * {@inheritdoc}
         */
        public function guess($path)
        {
            return $this->lazyLoadItself()->guess($path);
        }

        /**
         * {@inheritdoc}
         */
        public function addMimeTypeGuesser($guesser, $priority = 0)
        {
            return $this->lazyLoadItself()->addMimeTypeGuesser($guesser, $priority);
        }

        /**
         * {@inheritdoc}
         */
        public function addGuesser(MimeTypeGuesserInterface $guesser, $priority = 0)
        {
            return $this->lazyLoadItself()->addGuesser($guesser, $priority);
        }

        /**
         * {@inheritdoc}
         */
        public function isGuesserSupported(): bool
        {
            return $this->lazyLoadItself()->isGuesserSupported();
        }

        /**
         * {@inheritdoc}
         */
        public static function registerWithSymfonyGuesser(ContainerInterface $container)
        {
            \Drupal\Core\File\MimeType\MimeTypeGuesser::registerWithSymfonyGuesser($container);
        }

    }

}
