<?php
/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2026 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license <https://spdx.org/licenses/GPL-2.0-or-later.html> GPL-2.0-or-later
 * @link https://kalkun.sourceforge.io/
 */

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class InstallerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
		if (file_exists(FCPATH.'install') && $request->getUri()->getSegment(1) !== 'install')
		{
			return redirect()->to('install');
		}
	}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
