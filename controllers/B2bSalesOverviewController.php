<?php

namespace Grocy\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controlador de la Plataforma B2B (Enterprise Minimal)
 * Sistema de Ventas Mayoristas para Distribuidoras
 */
class B2bSalesOverviewController extends BaseController
{
	public function SalesOverview(Request $request, Response $response, array $args): Response
	{
		$urlManager = $this->AppContainer->get('UrlManager');
		$this->View->set('U', fn ($p) => $urlManager->ConstructUrl($p));
		$this->View->set('A', fn ($p) => $urlManager->ConstructUrl($p, true) . '?v=' . @filemtime(__DIR__ . '/../public' . $p));

		$path = $request->getUri()->getPath();
		$section = $args['section'] ?? '';

		if (empty($section))
		{
			if (str_contains($path, 'customers'))
			{
				$section = 'customers';
			}
			elseif (str_contains($path, 'products'))
			{
				$section = 'products';
			}
			elseif (str_contains($path, 'orders'))
			{
				$section = 'orders';
			}
			elseif (str_contains($path, 'invoices'))
			{
				$section = 'invoices';
			}
			elseif (str_contains($path, 'sales-reps'))
			{
				$section = 'sales-reps';
			}
			elseif (str_contains($path, 'profitability'))
			{
				$section = 'profitability';
			}
			elseif (str_contains($path, 'reports'))
			{
				$section = 'reports';
			}
			elseif (str_contains($path, 'settings'))
			{
				$section = 'settings';
			}
			elseif (str_contains($path, 'schedules'))
			{
				$section = 'schedules';
			}
			elseif (str_contains($path, 'employees'))
			{
				$section = 'employees';
			}
			elseif (str_contains($path, 'attendance'))
			{
				$section = 'attendance';
			}
			elseif (str_contains($path, 'shift-requests'))
			{
				$section = 'shift-requests';
			}
			else
			{
				$section = 'sales';
			}
		}

		$validSections = ['sales', 'customers', 'products', 'orders', 'invoices', 'sales-reps', 'profitability', 'reports', 'settings', 'schedules', 'employees', 'attendance', 'shift-requests'];
		if (!in_array($section, $validSections, true))
		{
			$section = 'sales';
		}

		return $this->View->Render($response, 'b2b.sales_overview', [
			'pageTitle' => 'Plataforma Mayorista B2B — Prince Distributors',
			'systemName' => 'PRINCE DISTRIBUTORS',
			'subTitle' => 'Wholesale B2B Suite',
			'demoTag' => 'DEMO',
			'activeSection' => $section
		]);
	}
}
