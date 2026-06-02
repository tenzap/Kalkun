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

class IsLoggedInFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
		$this->session = session();
		// session check
		if ($this->session->get('loggedin') === NULL)
		{
			if ($request->is('POST') && $request->getPost('idiom') !== NULL)
			{
				return redirect()->to('login?l='.$request->getPost('idiom'));
			}
			if ($request->is('GET') && $request->getGet('l') !== NULL)
			{
				return redirect()->to('login?l='.$request->getGet('l'));
			}
			$this->session->setFlashdata('bef_login_post_data', $request->getPost());
			$request_uri_qry_string = $request->getUri()->getQuery();
			if ( ! empty($request_uri_qry_string))
			{
				$request_uri_qry_string = '?'.$request_uri_qry_string;
			}
			return redirect()->to('login?r_url='.urlencode(current_url().$request_uri_qry_string));
		}
	}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
