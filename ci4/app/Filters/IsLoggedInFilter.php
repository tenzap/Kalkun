<?php

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
			if (service('request')->is('POST') && service('request')->getPost('idiom') !== NULL)
			{
				return redirect()->to('login?l='.$this->request->getPost('idiom'));
			}
			if (service('request')->is('GET') && service('request')->getGet('l') !== NULL)
			{
				return redirect()->to('login?l='.service('request')->getGet('l'));
			}
			$this->session->setFlashdata('bef_login_post_data', service('request')->getPost());
				$request_uri_qry_string = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
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
