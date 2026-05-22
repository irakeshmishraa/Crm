<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\FollowUpController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PipelineController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\GoogleSheetsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Client\ClientPortalController;
use App\Http\Controllers\InstallController;

// Installation
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'setupDatabase'])->name('database.setup');
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallController::class, 'setupAdmin'])->name('admin.setup');
    Route::get('/complete', [InstallController::class, 'complete'])->name('complete');
});


// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin
Route::middleware(['auth', 'two-factor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    Route::resource('leads', LeadController::class);
    Route::post('leads/import', [LeadController::class, 'import'])->name('leads.import');
    Route::get('leads/export/{format}', [LeadController::class, 'export'])->name('leads.export');
    Route::post('leads/bulk-assign', [LeadController::class, 'bulkAssign'])->name('leads.bulk-assign');
    Route::post('leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convertToClient'])->name('leads.convert');
    Route::post('leads/{lead}/score', [LeadController::class, 'updateScore'])->name('leads.score');
    Route::get('leads/{lead}/timeline', [LeadController::class, 'timeline'])->name('leads.timeline');
    Route::post('leads/duplicate-check', [LeadController::class, 'duplicateCheck'])->name('leads.duplicate-check');
    Route::post('leads/merge', [LeadController::class, 'merge'])->name('leads.merge');


    Route::resource('followups', FollowUpController::class);
    Route::post('followups/{followup}/complete', [FollowUpController::class, 'markComplete'])->name('followups.complete');
    Route::post('followups/{followup}/snooze', [FollowUpController::class, 'snooze'])->name('followups.snooze');
    Route::post('followups/{followup}/reschedule', [FollowUpController::class, 'reschedule'])->name('followups.reschedule');
    Route::get('followups-calendar', [FollowUpController::class, 'calendar'])->name('followups.calendar');
    Route::get('followups-kanban', [FollowUpController::class, 'kanban'])->name('followups.kanban');

    Route::resource('clients', ClientController::class);
    Route::get('clients/{client}/documents', [ClientController::class, 'documents'])->name('clients.documents');
    Route::post('clients/{client}/documents', [ClientController::class, 'uploadDocument'])->name('clients.documents.upload');
    Route::get('clients/{client}/payments', [ClientController::class, 'payments'])->name('clients.payments');
    Route::get('clients/{client}/communications', [ClientController::class, 'communications'])->name('clients.communications');

    Route::resource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
    Route::post('quotations/{quotation}/convert-invoice', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert-invoice');
    Route::get('quotations/{quotation}/versions', [QuotationController::class, 'versions'])->name('quotations.versions');

    Route::resource('products', ProductController::class);
    Route::get('product-categories', [ProductController::class, 'categories'])->name('products.categories');
    Route::post('product-categories', [ProductController::class, 'storeCategory'])->name('products.categories.store');

    Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::post('pipeline/update-stage', [PipelineController::class, 'updateStage'])->name('pipeline.update-stage');
    Route::get('pipeline/forecast', [PipelineController::class, 'forecast'])->name('pipeline.forecast');

    Route::resource('tasks', TaskController::class);
    Route::post('tasks/{task}/complete', [TaskController::class, 'markComplete'])->name('tasks.complete');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('calendar/events', [CalendarController::class, 'storeEvent'])->name('calendar.events.store');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/leads', [ReportController::class, 'leads'])->name('leads');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/followups', [ReportController::class, 'followups'])->name('followups');
        Route::get('/quotations', [ReportController::class, 'quotations'])->name('quotations');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/team', [ReportController::class, 'team'])->name('team');
        Route::get('/export/{type}/{format}', [ReportController::class, 'export'])->name('export');
    });


    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('team', [TeamController::class, 'index'])->name('team.index');
    Route::get('team/hierarchy', [TeamController::class, 'hierarchy'])->name('team.hierarchy');
    Route::get('team/performance', [TeamController::class, 'performance'])->name('team.performance');
    Route::get('team/activity-logs', [TeamController::class, 'activityLogs'])->name('team.activity-logs');

    Route::prefix('email')->name('email.')->group(function () {
        Route::get('/', [EmailController::class, 'inbox'])->name('inbox');
        Route::get('/sent', [EmailController::class, 'sent'])->name('sent');
        Route::get('/compose', [EmailController::class, 'compose'])->name('compose');
        Route::post('/send', [EmailController::class, 'send'])->name('send');
        Route::get('/templates', [EmailController::class, 'templates'])->name('templates');
        Route::post('/templates', [EmailController::class, 'storeTemplate'])->name('templates.store');
        Route::get('/thread/{threadId}', [EmailController::class, 'thread'])->name('thread');
        Route::post('/connect', [EmailController::class, 'connectGmail'])->name('connect');
    });

    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [WhatsAppController::class, 'index'])->name('index');
        Route::get('/chat/{contact}', [WhatsAppController::class, 'chat'])->name('chat');
        Route::post('/send', [WhatsAppController::class, 'send'])->name('send');
        Route::post('/bulk-send', [WhatsAppController::class, 'bulkSend'])->name('bulk-send');
        Route::get('/templates', [WhatsAppController::class, 'templates'])->name('templates');
        Route::post('/templates', [WhatsAppController::class, 'storeTemplate'])->name('templates.store');
        Route::get('/settings', [WhatsAppController::class, 'settings'])->name('settings');
        Route::post('/settings', [WhatsAppController::class, 'updateSettings'])->name('settings.update');
    });

    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/start', [CampaignController::class, 'start'])->name('campaigns.start');
    Route::post('campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('campaigns/{campaign}/resume', [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::get('campaigns/{campaign}/stats', [CampaignController::class, 'stats'])->name('campaigns.stats');


    Route::prefix('google-sheets')->name('google-sheets.')->group(function () {
        Route::get('/', [GoogleSheetsController::class, 'index'])->name('index');
        Route::post('/connect', [GoogleSheetsController::class, 'connect'])->name('connect');
        Route::get('/spreadsheets', [GoogleSheetsController::class, 'spreadsheets'])->name('spreadsheets');
        Route::post('/sync', [GoogleSheetsController::class, 'sync'])->name('sync');
        Route::post('/import', [GoogleSheetsController::class, 'import'])->name('import');
        Route::post('/export', [GoogleSheetsController::class, 'export'])->name('export');
        Route::get('/logs', [GoogleSheetsController::class, 'logs'])->name('logs');
        Route::post('/mapping', [GoogleSheetsController::class, 'saveMapping'])->name('mapping');
    });

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::post('notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/general', [SettingController::class, 'updateGeneral'])->name('general');
        Route::post('/email', [SettingController::class, 'updateEmail'])->name('email');
        Route::post('/google', [SettingController::class, 'updateGoogle'])->name('google');
        Route::post('/whatsapp', [SettingController::class, 'updateWhatsApp'])->name('whatsapp');
        Route::post('/branding', [SettingController::class, 'updateBranding'])->name('branding');
        Route::post('/backup', [BackupController::class, 'create'])->name('backup.create');
        Route::get('/backup/download/{file}', [BackupController::class, 'download'])->name('backup.download');
        Route::get('/white-label', [SettingController::class, 'whiteLabel'])->name('white-label');
        Route::post('/white-label', [SettingController::class, 'updateWhiteLabel'])->name('white-label.update');
    });

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::resource('webhooks', WebhookController::class);
});


// Client Portal
Route::prefix('portal')->name('portal.')->middleware('auth:client')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/quotations', [ClientPortalController::class, 'quotations'])->name('quotations');
    Route::get('/quotations/{quotation}', [ClientPortalController::class, 'viewQuotation'])->name('quotations.view');
    Route::post('/quotations/{quotation}/approve', [ClientPortalController::class, 'approveQuotation'])->name('quotations.approve');
    Route::post('/quotations/{quotation}/reject', [ClientPortalController::class, 'rejectQuotation'])->name('quotations.reject');
    Route::get('/documents', [ClientPortalController::class, 'documents'])->name('documents');
    Route::post('/support', [ClientPortalController::class, 'submitSupport'])->name('support');
});

// Public quotation view
Route::get('/quotation/{token}', [QuotationController::class, 'publicView'])->name('quotation.public');
Route::post('/quotation/{token}/approve', [QuotationController::class, 'publicApprove'])->name('quotation.public.approve');

// Lead Capture Forms (Public)
Route::post('/lead-capture/contact', [LeadController::class, 'captureContact'])->name('lead-capture.contact');
Route::post('/lead-capture/callback', [LeadController::class, 'captureCallback'])->name('lead-capture.callback');
Route::post('/lead-capture/demo', [LeadController::class, 'captureDemo'])->name('lead-capture.demo');
Route::post('/lead-capture/webhook', [LeadController::class, 'captureWebhook'])->name('lead-capture.webhook');

Route::get('/', fn() => redirect()->route('login'));
