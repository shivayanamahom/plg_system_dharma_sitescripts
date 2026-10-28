<?php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\DharmaSiteScripts\Extension\DharmaSiteScripts;

require_once dirname(__DIR__) . '/src/Extension/DharmaSiteScripts.php';

return new class implements ServiceProviderInterface
{
	public function register(Container $container): void
	{
		$container->set(PluginInterface::class, function (Container $container) {
			$plugin = new DharmaSiteScripts(
				$container->get(DispatcherInterface::class),
				(array) PluginHelper::getPlugin('system', 'dharmasitescripts')
			);
			$plugin->setApplication(Factory::getApplication());

			return $plugin;
		});
	}
};
