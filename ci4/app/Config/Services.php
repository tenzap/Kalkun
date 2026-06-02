<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Config\Services as AppServices;
use Locale;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

     public static function language(?string $locale = null, bool $getShared = true)
     {
         // Identical to vendor/codeigniter4/framework/system/Config/Services.php
         // Except that we call \App\Libraries\Language instead of Language
         if ($getShared) {
             return static::getSharedInstance('language', $locale)->setLocale($locale);
         }

         if (AppServices::get('request') instanceof IncomingRequest) {
             $requestLocale = AppServices::get('request')->getLocale();
         } else {
             $requestLocale = Locale::getDefault();
         }

         // Use '?:' for empty string check
         $locale = in_array($locale, [null, '', '0'], true) ? $requestLocale : $locale;

         return new \App\Libraries\Language($locale);
     }

}
