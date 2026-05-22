<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    public function index() { if (file_exists(storage_path('installed'))) return redirect('/login'); return view('install.index'); }
    public function requirements() {
        $reqs = ['PHP >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='), 'BCMath' => extension_loaded('bcmath'), 'JSON' => extension_loaded('json'), 'Mbstring' => extension_loaded('mbstring'), 'OpenSSL' => extension_loaded('openssl'), 'PDO MySQL' => extension_loaded('pdo_mysql'), 'Curl' => extension_loaded('curl'), 'GD' => extension_loaded('gd'), 'Storage Writable' => is_writable(storage_path())];
        return view('install.requirements', ['requirements' => $reqs, 'allPassed' => !in_array(false, $reqs)]);
    }
    public function database() { return view('install.database'); }
    public function setupDatabase(Request $request) {
        $request->validate(['db_host' => 'required', 'db_port' => 'required|integer', 'db_name' => 'required', 'db_user' => 'required']);
        try { new \PDO("mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}", $request->db_user, $request->db_password); } catch (\Exception $e) { return back()->withErrors(['db' => 'Connection failed: ' . $e->getMessage()]); }
        $this->updateEnv(['DB_HOST' => $request->db_host, 'DB_PORT' => $request->db_port, 'DB_DATABASE' => $request->db_name, 'DB_USERNAME' => $request->db_user, 'DB_PASSWORD' => $request->db_password ?? '']);
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);
        return redirect()->route('install.admin');
    }
    public function admin() { return view('install.admin'); }
    public function setupAdmin(Request $request) {
        $request->validate(['name' => 'required|string', 'email' => 'required|email', 'password' => 'required|string|min:8|confirmed']);
        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'username' => 'admin', 'email_verified_at' => now()]);
        $role = Role::where('slug', 'super-admin')->first();
        if ($role) $user->roles()->attach($role->id);
        Artisan::call('key:generate', ['--force' => true]);
        file_put_contents(storage_path('installed'), date('Y-m-d H:i:s'));
        return redirect()->route('install.complete');
    }
    public function complete() { return view('install.complete'); }
    private function updateEnv(array $data): void { $env = file_get_contents(base_path('.env')); foreach ($data as $k => $v) { $v = str_contains($v, ' ') ? "\"{$v}\"" : $v; $env = preg_match("/^{$k}=.*/m", $env) ? preg_replace("/^{$k}=.*/m", "{$k}={$v}", $env) : $env . "\n{$k}={$v}"; } file_put_contents(base_path('.env'), $env); }
}
