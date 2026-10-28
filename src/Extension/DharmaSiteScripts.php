<?php

namespace Joomla\Plugin\System\DharmaSiteScripts\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

final class DharmaSiteScripts extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeCompileHead' => 'onBeforeCompileHead',
			'onAfterRender'      => 'onAfterRender',
		];
	}

	public function onBeforeCompileHead(BeforeCompileHeadEvent $event): void
	{
		$app = $event->getApplication();
		if (!$app->isClient('site') || $event->getDocument()->getType() !== 'html')
		{
			return;
		}

		foreach ($this->getCodes('head') as $code)
		{
			$event->getDocument()->addCustomTag($code);
		}
	}

	public function onAfterRender(AfterRenderEvent $event): void
	{
		$app = $event->getApplication();

		if (!$app->isClient('site') || $app->getDocument()->getType() !== 'html')
		{
			return;
		}

		$codes = $this->getCodes('body');

		if (!$codes)
		{
			return;
		}

		$body = (string) $app->getBody();

		if (!preg_match_all('~</body\s*>~i', $body, $matches, PREG_OFFSET_CAPTURE))
		{
			return;
		}

		$closing = end($matches[0]);
		$offset = $closing[1];
		$injection = "\n" . implode("\n", $codes) . "\n";
		$app->setBody(substr($body, 0, $offset) . $injection . substr($body, $offset));
	}

	/**
	 * Return enabled code snippets for the selected insertion point.
	 */
	private function getCodes(string $position): array
	{
		$rows = $this->params->get('codes', []);

		if (is_string($rows))
		{
			$rows = json_decode($rows, true) ?: [];
		}
		elseif (is_object($rows))
		{
			$rows = (array) $rows;
		}

		if (!is_array($rows))
		{
			return [];
		}

		$codes = [];

		foreach ($rows as $row)
		{
			if (is_object($row))
			{
				$row = (array) $row;
			}

			if (!is_array($row)
				|| (int) ($row['enabled'] ?? 1) !== 1
				|| ($row['position'] ?? 'body') !== $position
			)
			{
				continue;
			}

			$code = trim((string) ($row['code'] ?? ''));

			if ($code !== '')
			{
				$codes[] = $code;
			}
		}

		return $codes;
	}
}
