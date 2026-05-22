<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BackupController extends Controller
{
    public function create()
    {
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        if (!is_dir(storage_path('app/backups'))) mkdir(storage_path('app/backups'), 0755, true);
        $cmd = sprintf('mysqldump -u%s -p%s %s > %s', config('database.connections.mysql.username'), config('database.connections.mysql.password'), config('database.connections.mysql.database'), $path);
        exec($cmd, $output, $ret);
        return $ret === 0 ? back()->with('success', "Backup: {$filename}") : back()->withErrors(['backup' => 'Failed.']);
    }
    public function download(string $file) { $path = storage_path('app/backups/' . $file); return file_exists($path) ? response()->download($path) : back()->withErrors(['backup' => 'Not found.']); }
}
