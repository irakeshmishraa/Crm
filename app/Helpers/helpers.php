<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting(string $key, $default = null) { return Setting::get($key, $default); }
}
if (!function_exists('format_currency')) {
    function format_currency($amount, $symbol = null): string { $symbol = $symbol ?? config('crm.currency_symbol', '₹'); return $symbol . ' ' . number_format((float)$amount, 2); }
}
if (!function_exists('format_date')) {
    function format_date($date, $format = null): string { if (!$date) return '-'; return \Carbon\Carbon::parse($date)->format($format ?? config('crm.date_format', 'd-m-Y')); }
}
if (!function_exists('format_datetime')) {
    function format_datetime($datetime): string { if (!$datetime) return '-'; return \Carbon\Carbon::parse($datetime)->format(config('crm.date_format', 'd-m-Y') . ' ' . config('crm.time_format', 'h:i A')); }
}
if (!function_exists('app_name')) {
    function app_name(): string { return config('crm.white_label.enabled') ? config('crm.white_label.name', 'SmartLead CRM Pro') : config('app.name', 'SmartLead CRM Pro'); }
}
if (!function_exists('app_logo')) {
    function app_logo(): string { $logo = Setting::get('company_logo'); if (config('crm.white_label.enabled') && config('crm.white_label.logo')) return asset('storage/' . config('crm.white_label.logo')); return $logo ? asset('storage/' . $logo) : asset('images/logo.png'); }
}
