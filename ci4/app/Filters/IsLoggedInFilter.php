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
use App\Libraries\Language;

class IsLoggedInFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
		$session = session();
		// session check
		if ($session->get('loggedin') === NULL)
		{
			if ($request->is('POST') && $request->getPost('idiom') !== NULL)
			{
				return redirect()->to('login?l='.$request->getPost('idiom'));
			}
			if ($request->is('GET') && $request->getGet('l') !== NULL)
			{
				return redirect()->to('login?l='.$request->getGet('l'));
			}
			$session->setFlashdata('bef_login_post_data', $request->getPost());
			$request_uri_qry_string = $request->getUri()->getQuery();
			if ( ! empty($request_uri_qry_string))
			{
				$request_uri_qry_string = '?'.$request_uri_qry_string;
			}
			return redirect()->to('login?r_url='.urlencode(current_url().$request_uri_qry_string));
		}

		if ($request->getUri()->getSegment(1) === 'users' && $session->get('loggedin') === 'TRUE')
		{
			// check level
			if ($session->get('level') !== 'admin')
			{
				$Kalkun_model = model('KalkunModel');

				// language
				helper('i18n');
				$lang = $Kalkun_model->get_setting()->getRow('language') ?? 'english';
				$locale = Language::$idiom_to_locale[$lang];
				service('language', $locale)->load('kalkun_lang');

				$session->setFlashdata('notif', tr_raw('Access denied.'));
				return redirect()->to('/');
			}
		}

	}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
