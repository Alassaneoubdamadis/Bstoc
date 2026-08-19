<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\SettingResource;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\State;
use App\Models\Warehouse;
use App\Repositories\SettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Class SettingAPIController
 */
class SettingAPIController extends AppBaseController
{
    /** @var SettingRepository */
    private $settingRepository;

    public function __construct(SettingRepository $productRepository)
    {
        $this->settingRepository = $productRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $settings['logo'] = getLogoUrl();
        $settings['warehouse_name'] = Warehouse::whereId($settings['default_warehouse'] ?? null)->first()->name ?? '';
        $settings['customer_name'] = Customer::whereId($settings['default_customer'] ?? null)->first()->name ?? '';
        $settings['currency_symbol'] = Currency::whereId($settings['currency'] ?? null)->first()->symbol ?? '';
        $settings['countries'] = Country::all();

        return $this->sendResponse(new SettingResource(['type' => 'settings', 'attributes' => $settings]),
            'Setting data retrieved successfully.');
    }

    public function update(Request $request): JsonResponse
    {
        if (! company_allows('manage_setting')) {
            return $this->sendError('Le super-admin n’a pas autorisé ce magasin à modifier les réglages.', 403);
        }
        $input = $request->all();
        $settings = $this->settingRepository->updateSettings($input);

        return $this->sendResponse(new SettingResource(['type' => 'settings', 'attributes' => $settings]),
            'Setting data updated successfully');
    }

    public function clearCache(): JsonResponse
    {
        Artisan::call('cache:clear');

        return $this->sendSuccess(__('messages.success.cache_clear_successfully'));
    }

    public function getFrontSettingsValue(): JsonResponse
    {
        $keyName = [
            'currency', 'email', 'company_name', 'phone', 'developed', 'footer', 'default_language', 'default_customer',
            'default_warehouse', 'address', 'show_app_name_in_sidebar'
        ];
        $settings = Setting::whereIn('key', $keyName)->pluck('value', 'key')->toArray();
        $settings['logo'] = getLogoUrl();
        if (empty($settings['company_name'])) {
            $settings['company_name'] = platform_app_name();
        }
        if (($settings['show_app_name_in_sidebar'] ?? '0') !== '1' && platform_logo_url() !== '' && $settings['logo'] === platform_logo_url()) {
            $settings['show_app_name_in_sidebar'] = '1';
            $settings['company_name'] = platform_app_name();
        }
        $settings['warehouse_name'] = Warehouse::whereId($settings['default_warehouse'] ?? null)->first()->name ?? '';
        $settings['customer_name'] = Customer::whereId($settings['default_customer'] ?? null)->first()->name ?? '';
        $settings['currency_symbol'] = Currency::whereId($settings['currency'] ?? null)->first()->symbol ?? '';

        return $this->sendResponse(new SettingResource(['type' => 'settings', 'value' => $settings]),
            'Setting value retrieved successfully.');
    }

    public function getStates($countryId): JsonResponse
    {
        $states = State::whereCountryId($countryId)->pluck('name');

        return $this->sendResponse(new SettingResource(['type' => 'states', 'value' => $states]),
            'States retrieved successfully.');
    }

    public function getMailSettings()
    {
        $envData = $this->settingRepository->getEnvData();

        return $this->sendResponse($envData, 'Mail Credential Retrieved Successfully');
    }

    public function updateMailSettings(Request $request): JsonResponse
    {
        $request->validate([
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from_address', 'mail_encryption',
        ]);
        $this->settingRepository->updateMailEnvSetting($request->all());

        Artisan::call('optimize:clear');
        Artisan::call('config:cache');

        return $this->sendSuccess('Mail Settings Save Successfully');
    }
}
